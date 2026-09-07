# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-fivem-node22
# ----------------------------------

FROM ghcr.io/sparkedhost/images:nodejs-22 AS node

FROM ghcr.io/sparkedhost/images:games-fivem

COPY --from=node /usr/local/ /usr/local/
