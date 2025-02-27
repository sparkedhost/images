import os
import yaml

def get_image_details():
    """Prompts the user for image name and target folder."""
    image_name = input("Enter the name of the image (e.g., my-new-image): ")
    target_folder = input("Enter the target folder (leave empty for current directory): ").strip()
    return image_name, target_folder

def setup_folders(image_name, target_folder):
    """Creates the image folder and determines base folder."""
    if target_folder:
        base_folder = os.path.join(os.getcwd(), target_folder, image_name)
    else:
        base_folder = os.path.join(os.getcwd(), image_name)

    os.makedirs(base_folder, exist_ok=True)
    print(f"Created base folder: {base_folder}")
    return base_folder

def handle_entrypoint(base_folder):
    """Handles entrypoint.sh creation or usage of existing one."""
    entrypoint_path = os.path.join(base_folder, "entrypoint.sh")
    if os.path.exists(entrypoint_path):
        print(f"Using existing entrypoint.sh: {entrypoint_path}")
        return True

    default_entrypoint = """#!/usr/bin/env bash
cd /home/container

# Make internal Docker IP address available to processes
export INTERNAL_IP=`ip route get 1 | awk '{print $(NF-2);exit}'`

# Evaluate startup variables.
MODIFIED_STARTUP=$(eval "echo \"$(echo ${STARTUP} | sed -e 's/{{/${{/g' -e 's/}}/}}/g')\"")
echo "customer@apollopanel:~# ${MODIFIED_STARTUP}"

eval "${MODIFIED_STARTUP}"
"""
    with open(entrypoint_path, "w") as f:
        f.write(default_entrypoint)
    os.chmod(entrypoint_path, 0o755)  # Make executable
    print(f"Created entrypoint.sh: {entrypoint_path}")
    return False

def create_dockerfile(version_folder):
    """Creates a placeholder Dockerfile."""
    dockerfile_path = os.path.join(version_folder, "Dockerfile")
    if not os.path.exists(dockerfile_path):
        with open(dockerfile_path, "w") as f:
            f.write("# Placeholder Dockerfile - Please configure!\n")
        print(f"Created placeholder Dockerfile: {dockerfile_path}")
    return dockerfile_path

def get_matrix_input():
    """Prompts user for matrix configuration."""
    use_matrix = input("Do you want to create a matrix-based workflow? (yes/no): ").lower() == "yes"
    if use_matrix:
        matrix_values = input("Enter comma-separated values for the matrix (e.g., 16,17,18): ").split(",")
        return use_matrix, matrix_values
    return use_matrix, None

def create_workflow_data(image_name, use_matrix, matrix_values, base_folder, use_existing_entrypoint):
    """Generates the workflow data dictionary."""
    base_folder_rel = os.path.relpath(base_folder, os.getcwd())
    if use_matrix:
        image_name_base = image_name.split("-")[0]  # Remove version from image name
        workflow_data = {
            "name": f"Build {image_name_base} images",
            "on": {
                "workflow_dispatch": {},
                "push": {
                    "branches": ["main"],
                    "paths": [
                        f"{base_folder_rel}/**/*",
                        f"{base_folder_rel}/entrypoint.sh",
                        f".github/workflows/{image_name_base}.yml"
                    ]
                }
            },
            "jobs": {
                "build": {
                    "name": f"{image_name_base} ${{ matrix.version }}",
                    "runs-on": "ubuntu-latest",
                    "strategy": {
                        "fail-fast": False,
                        "matrix": {
                            "version": [v.strip() for v in matrix_values]
                        }
                    },
                    "steps": [
                        {
                            "uses": "actions/checkout@v3"
                        },
                        {
                            "uses": "docker/setup-buildx-action@v2",
                            "with": {
                                "version": "v0.9.1",
                                "buildkitd-flags": "--debug"
                            }
                        },
                        {
                            "uses": "docker/login-action@v2",
                            "with": {
                                "registry": "ghcr.io",
                                "username": "${{ github.repository_owner }}",
                                "password": "${{ secrets.GITHUB_TOKEN }}"
                            }
                        },
                        {
                            "uses": "docker/build-push-action@v3",
                            "with": {
                                "context": f"./{base_folder_rel}/{image_name_base}-${{ matrix.version }}",
                                "file": f"./{base_folder_rel}/{image_name_base}-${{ matrix.version }}/Dockerfile",
                                "platforms": "linux/amd64",
                                "push": True,
                                "tags": f"ghcr.io/sparkedhost/images:{image_name_base}-${{ matrix.version }}"
                            }
                        }
                    ]
                }
            }
        }
    else:
        workflow_data = {
            "name": f"Build {image_name} image",
            "on": {
                "workflow_dispatch": {},
                "push": {
                    "branches": ["main"],
                    "paths": [
                        f"{base_folder_rel}/*",
                        f".github/workflows/{image_name}.yml"
                    ]
                }
            },
            "jobs": {
                "push": {
                    "name": f"{image_name}",
                    "runs-on": "ubuntu-latest",
                    "steps": [
                        {
                            "uses": "actions/checkout@v3"
                        },
                        {
                            "uses": "docker/setup-buildx-action@v2",
                            "with": {
                                "version": "v0.9.1",
                                "buildkitd-flags": "--debug"
                            }
                        },
                        {
                            "uses": "docker/login-action@v2",
                            "with": {
                                "registry": "ghcr.io",
                                "username": "${{ github.repository_owner }}",
                                "password": "${{ secrets.GITHUB_TOKEN }}"
                            }
                        },
                        {
                            "uses": "docker/build-push-action@v3",
                            "with": {
                                "context": f"./{base_folder_rel}",
                                "file": f"./{base_folder_rel}/Dockerfile",
                                "platforms": "linux/amd64",
                                "push": True,
                                "tags": f"ghcr.io/sparkedhost/images:{image_name}"
                            }
                        }
                    ]
                }
            }
        }
    return workflow_data

def create_workflow_file(image_name, workflow_data):
    """Creates the workflow file."""
    workflows_dir = os.path.join(os.getcwd(), ".github", "workflows")
    os.makedirs(workflows_dir, exist_ok=True)
    workflow_file_path = os.path.join(workflows_dir, f"{image_name}.yml")

    if os.path.exists(workflow_file_path):
        print(f"Workflow file already exists: {workflow_file_path}")
        return

    # Dump the YAML data to a string
    yaml_str = yaml.dump(workflow_data, indent=2, sort_keys=False, default_flow_style=False)

    # Replace problematic parts
    yaml_str = yaml_str.replace("workflow_dispatch: {}", "workflow_dispatch:")
    yaml_str = yaml_str.replace("'on':", "on:")

    # Write the modified YAML string to the file
    with open(workflow_file_path, "w") as f:
        f.write(yaml_str)
    print(f"Created workflow file: {workflow_file_path}")

def create_image_wizard():
    """
    Guides the user through the process of creating a new image, generating
    the necessary files and directory structure.
    """

    print("!!! Use with caution - untested !!!\n")
    print("This script can be useful for starting a new image, but always verify what is created.\n")

    image_name, target_folder = get_image_details()
    base_folder = setup_folders(image_name, target_folder)
    use_existing_entrypoint = handle_entrypoint(base_folder)
    use_matrix, matrix_values = get_matrix_input()

    if use_matrix:
        image_name_base = image_name.split("-")[0]  # Remove version from image name
        for version in matrix_values:
            version_folder = os.path.join(base_folder, f"{image_name_base}-{version.strip()}")
            os.makedirs(version_folder, exist_ok=True)
            create_dockerfile(version_folder)
    else:
        create_dockerfile(base_folder)

    workflow_data = create_workflow_data(image_name, use_matrix, matrix_values, base_folder, use_existing_entrypoint)
    create_workflow_file(image_name, workflow_data)

    print("\nImage creation process complete!")
    print(f"Remember to configure workflow, entrypoint and Dockerfile and push to GitHub.")

if __name__ == "__main__":
    create_image_wizard()
