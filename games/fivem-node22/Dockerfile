# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-fivem-node22
# ----------------------------------

FROM node:22-bullseye-slim AS node

FROM ghcr.io/sparkedhost/images:games-fivem

COPY --from=node /usr/local/ /usr/local/
