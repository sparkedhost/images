# Apollo Images
#### Docker images designed for use with Sparked Host's Apollo Panel.

## Supported tags

### Java images

* [`java-jre8`](https://github.com/sparkedhost/images/blob/main/java/java-jre8/Dockerfile)
* [`java-jre11`](https://github.com/sparkedhost/images/blob/main/java/java-jre11/Dockerfile)
* [`java-jre16`](https://github.com/sparkedhost/images/blob/main/java/java-jre16/Dockerfile)
* [`java-jre17`](https://github.com/sparkedhost/images/blob/main/java/java-jre17/Dockerfile)
* [`java-jre18`](https://github.com/sparkedhost/images/blob/main/java/java-jre18/Dockerfile)
* [`lavalink`](https://github.com/sparkedhost/images/blob/main/java/lavalink/Dockerfile)

### Node.js images

* [`nodejs-12`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-12/Dockerfile)
* [`nodejs-13`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-13/Dockerfile)
* [`nodejs-14`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-14/Dockerfile)
* [`nodejs-15`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-15/Dockerfile)
* [`nodejs-16`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-16/Dockerfile)
* [`nodejs-17`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-17/Dockerfile)
* [`nodejs-18`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-18/Dockerfile)
* [`nodejs-19`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-19/Dockerfile)

### Python images

* [`python-3.6`](https://github.com/sparkedhost/images/blob/main/python/python-3.6/Dockerfile)
* [`python-3.7`](https://github.com/sparkedhost/images/blob/main/python/python-3.7/Dockerfile)
* [`python-3.8`](https://github.com/sparkedhost/images/blob/main/python/python-3.8/Dockerfile)
* [`python-3.9`](https://github.com/sparkedhost/images/blob/main/python/python-3.9/Dockerfile)
* [`python-3.10`](https://github.com/sparkedhost/images/blob/main/python/python-3.10/Dockerfile)
* [`python-3.11`](https://github.com/sparkedhost/images/blob/main/python/python-3.11/Dockerfile)

### Other games images

* [`games-unturned`](https://github.com/sparkedhost/images/blob/main/games/unturned/Dockerfile)

### Generic images

* [`generic-debian`](https://github.com/sparkedhost/images/blob/main/generic/debian/Dockerfile)
* [`mono-generic`](https://github.com/sparkedhost/images/blob/main/mono/mono-generic/Dockerfile)
* [`wine-generic`](https://github.com/sparkedhost/images/blob/main/wine/wine-generic/Dockerfile)

## Requesting changes

Docker caches images locally on every machine they're pulled on, even old tags that aren't in use anymore.
This means we have to keep these images as small as possible. That being said, if you find a problem in our
images that is breaking normal functionality, or have a feature request that you believe is only going to
marginally increase the size, please feel free to create an issue or submit a pull request, we're always
open to new ideas.
