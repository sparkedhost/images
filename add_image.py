import os
import textwrap


def get_image_details():
    """Prompts the user for image name and target folder."""
    image_name = input("Enter the image name (e.g., games-fivem, nodejs, mariadb): ").strip()
    target_folder = input("Enter the target folder relative to repo root (e.g., games/fivem, nodejs, databases/mariadb): ").strip()
    return image_name, target_folder


def setup_folders(target_folder):
    """Creates the image folder and returns the base folder path."""
    base_folder = os.path.join(os.getcwd(), target_folder)
    os.makedirs(base_folder, exist_ok=True)
    print(f"Created base folder: {base_folder}")
    return base_folder


def handle_entrypoint(base_folder, target_folder):
    """Handles entrypoint.sh creation or usage of existing one.

    The entrypoint lives in the parent context directory (e.g. games/ or nodejs/).
    For nested targets like games/fivem the entrypoint goes in games/fivem/.
    For matrix targets like nodejs/ the entrypoint goes in nodejs/.
    """
    entrypoint_path = os.path.join(base_folder, "entrypoint.sh")

    # Also check parent directory for shared entrypoints (e.g. games/ has per-game entrypoints)
    parent_entrypoint = os.path.join(os.path.dirname(base_folder), "entrypoint.sh")

    if os.path.exists(entrypoint_path):
        print(f"Using existing entrypoint.sh: {entrypoint_path}")
        return True
    if os.path.exists(parent_entrypoint):
        print(f"Found shared entrypoint.sh in parent: {parent_entrypoint}")
        return True

    default_entrypoint = textwrap.dedent("""\
        #!/bin/bash
        cd /home/container

        # Make internal Docker IP address available to processes.
        export INTERNAL_IP=$(ip route get 1 | awk '{print $(NF-2);exit}')

        # Replace Startup variables.
        MODIFIED_STARTUP=$(echo -e ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')
        echo "customer@apollopanel:~# ${MODIFIED_STARTUP}"

        # Run the Server.
        eval ${MODIFIED_STARTUP}
    """)
    with open(entrypoint_path, "w", newline="\n") as f:
        f.write(default_entrypoint)
    os.chmod(entrypoint_path, 0o755)
    print(f"Created entrypoint.sh: {entrypoint_path}")
    return False


def create_dockerfile(folder, image_tag, entrypoint_copy_path):
    """Creates a template Dockerfile."""
    dockerfile_path = os.path.join(folder, "Dockerfile")
    if os.path.exists(dockerfile_path):
        print(f"Dockerfile already exists: {dockerfile_path}")
        return dockerfile_path

    content = textwrap.dedent(f"""\
        # ----------------------------------
        # Sparked Host Custom Image
        # Image: ghcr.io/sparkedhost/images:{image_tag}
        # ----------------------------------

        FROM debian:bookworm-slim

        LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

        ENV DEBIAN_FRONTEND noninteractive

        RUN useradd -m -d /home/container -s /bin/bash container

        RUN apt update \\
         && apt upgrade -y \\
         && apt install -y curl ca-certificates iproute2 git

        USER container
        ENV  USER=container HOME=/home/container

        WORKDIR /home/container

        COPY {entrypoint_copy_path} /entrypoint.sh

        CMD ["/bin/bash", "/entrypoint.sh"]
    """)
    with open(dockerfile_path, "w", newline="\n") as f:
        f.write(content)
    print(f"Created Dockerfile: {dockerfile_path}")
    return dockerfile_path


def get_matrix_input():
    """Prompts user for matrix configuration."""
    use_matrix = input("Create a matrix-based workflow with multiple versions? (yes/no): ").lower() == "yes"
    if use_matrix:
        matrix_values = input("Enter comma-separated versions (e.g., 16,17,18): ").split(",")
        return use_matrix, [v.strip() for v in matrix_values]
    return use_matrix, None


def generate_workflow_yaml(image_name, use_matrix, matrix_values, target_folder):
    """Generates the workflow YAML string using the reusable docker-build.yml."""
    # Determine the context directory (the parent that contains the Dockerfile and entrypoint)
    # For matrix: context is the target_folder itself (e.g., ./nodejs, ./databases/mariadb)
    # For non-matrix: context is the parent of the target (e.g., ./games for games/fivem)
    parts = target_folder.split("/")

    if use_matrix:
        image_name_base = parts[-1]  # e.g., "nodejs", "mariadb"
        context = f"./{target_folder}"
        paths_trigger = f"{target_folder}/**/*"

        # Convert matrix values to YAML list with proper quoting for floats
        version_lines = ""
        for v in matrix_values:
            # Quote values that look like floats to preserve them (e.g., 3.10 vs 3.1)
            if "." in v:
                version_lines += f'          - "{v}"\n'
            else:
                version_lines += f"          - {v}\n"

        workflow = textwrap.dedent(f"""\
            name: Build {image_name_base.replace("-", " ").title()}

            on:
              workflow_dispatch:
              push:
                branches:
                  - main
                paths:
                  - "{paths_trigger}"
                  - .github/workflows/{image_name}.yml

            jobs:
              build:
                name: "{image_name_base} ${{{{ matrix.version }}}}"
                strategy:
                  fail-fast: false
                  matrix:
                    version:
            {version_lines.rstrip()}
                uses: ./.github/workflows/docker-build.yml
                with:
                  context: {context}
                  file: {context}/{image_name_base}-${{{{ matrix.version }}}}/Dockerfile
                  tags: ghcr.io/sparkedhost/images:{image_name_base}-${{{{ matrix.version }}}}
                secrets: inherit
        """)
    else:
        # Non-matrix: e.g., games/fivem -> context=./games, file=./games/fivem/Dockerfile
        if len(parts) > 1:
            context = f"./{parts[0]}"
            subfolder = "/".join(parts[1:])
            file_path = f"{context}/{subfolder}/Dockerfile"
            paths_trigger = f"{target_folder}/*"
        else:
            context = f"./{target_folder}"
            file_path = f"{context}/Dockerfile"
            paths_trigger = f"{target_folder}/*"

        workflow = textwrap.dedent(f"""\
            name: Build {image_name}

            on:
              workflow_dispatch:
              push:
                branches:
                  - main
                paths:
                  - "{paths_trigger}"
                  - .github/workflows/{image_name}.yml

            jobs:
              build:
                uses: ./.github/workflows/docker-build.yml
                with:
                  context: {context}
                  file: {file_path}
                  tags: ghcr.io/sparkedhost/images:{image_name}
        """)

    return workflow


def create_workflow_file(image_name, workflow_yaml):
    """Creates the workflow file."""
    workflows_dir = os.path.join(os.getcwd(), ".github", "workflows")
    os.makedirs(workflows_dir, exist_ok=True)
    workflow_file_path = os.path.join(workflows_dir, f"{image_name}.yml")

    if os.path.exists(workflow_file_path):
        print(f"Workflow file already exists: {workflow_file_path}")
        return

    with open(workflow_file_path, "w", newline="\n") as f:
        f.write(workflow_yaml)
    print(f"Created workflow file: {workflow_file_path}")


def create_image_wizard():
    """
    Guides the user through the process of creating a new image, generating
    the necessary files and directory structure.
    """
    print("=== Sparked Host Image Creator ===\n")
    print("This script creates the folder structure, Dockerfile(s), entrypoint,")
    print("and GitHub Actions workflow for a new image.\n")
    print("Always verify the generated files before pushing.\n")

    image_name, target_folder = get_image_details()
    base_folder = setup_folders(target_folder)
    handle_entrypoint(base_folder, target_folder)
    use_matrix, matrix_values = get_matrix_input()

    parts = target_folder.split("/")

    if use_matrix:
        image_name_base = parts[-1]
        for version in matrix_values:
            version_folder = os.path.join(base_folder, f"{image_name_base}-{version}")
            os.makedirs(version_folder, exist_ok=True)
            create_dockerfile(version_folder, f"{image_name_base}-{version}", "./entrypoint.sh")
    else:
        # For nested folders (e.g., games/fivem), the COPY path references
        # relative to the context dir (e.g., ./fivem/entrypoint.sh)
        if len(parts) > 1:
            subfolder = "/".join(parts[1:])
            entrypoint_copy = f"./{subfolder}/entrypoint.sh"
        else:
            entrypoint_copy = "./entrypoint.sh"
        create_dockerfile(base_folder, image_name, entrypoint_copy)

    workflow_yaml = generate_workflow_yaml(image_name, use_matrix, matrix_values, target_folder)
    create_workflow_file(image_name, workflow_yaml)

    print("\n✔ Image creation complete!")
    print("Remember to configure the Dockerfile and entrypoint, then push to GitHub.")


if __name__ == "__main__":
    create_image_wizard()
