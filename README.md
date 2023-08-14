# Apollo Images

#### Docker images designed for use with Sparked Host's Apollo Panel.

## Supported tags

### Java images

- [`java-jre8`](https://github.com/sparkedhost/images/blob/main/java/java-jre8/Dockerfile)
- [`java-jre11`](https://github.com/sparkedhost/images/blob/main/java/java-jre11/Dockerfile)
- [`java-jre16`](https://github.com/sparkedhost/images/blob/main/java/java-jre16/Dockerfile)[^eol]
- [`java-jre17`](https://github.com/sparkedhost/images/blob/main/java/java-jre17/Dockerfile)
- [`java-jre18`](https://github.com/sparkedhost/images/blob/main/java/java-jre18/Dockerfile)[^eol]
- [`java-jre19`](https://github.com/sparkedhost/images/blob/main/java/java-jre19/Dockerfile)[^eol]
- [`java-jre20`](https://github.com/sparkedhost/images/blob/main/java/java-jre20/Dockerfile)
- [`anti-malware`](https://github.com/sparkedhost/images/blob/main/java/anti-malware/Dockerfile)[^antimalware]

### Node.js images

- [`nodejs-12`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-12/Dockerfile)[^eol]
- [`nodejs-13`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-13/Dockerfile)[^eol]
- [`nodejs-14`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-14/Dockerfile)[^eol]
- [`nodejs-15`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-15/Dockerfile)[^eol]
- [`nodejs-16`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-16/Dockerfile)
- [`nodejs-17`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-17/Dockerfile)[^eol]
- [`nodejs-18`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-18/Dockerfile)
- [`nodejs-19`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-19/Dockerfile)[^eol]
- [`nodejs-20`](https://github.com/sparkedhost/images/blob/main/nodejs/nodejs-20/Dockerfile)

### Python images

- [`python-3.6`](https://github.com/sparkedhost/images/blob/main/python/python-3.6/Dockerfile)[^eol]
- [`python-3.7`](https://github.com/sparkedhost/images/blob/main/python/python-3.7/Dockerfile)[^eol]
- [`python-3.8`](https://github.com/sparkedhost/images/blob/main/python/python-3.8/Dockerfile)
- [`python-3.9`](https://github.com/sparkedhost/images/blob/main/python/python-3.9/Dockerfile)
- [`python-3.10`](https://github.com/sparkedhost/images/blob/main/python/python-3.10/Dockerfile)
- [`python-3.11`](https://github.com/sparkedhost/images/blob/main/python/python-3.11/Dockerfile)

### Game Hosting images

- [`games-rust`](https://github.com/sparkedhost/images/blob/main/games/rust/Dockerfile)
- [`games-unturned`](https://github.com/sparkedhost/images/blob/main/games/unturned/Dockerfile)
- [`games-unturned-preview`](https://github.com/sparkedhost/images/blob/main/games/unturned-preview/Dockerfile)
- [`games-liteloader-bds`](https://github.com/sparkedhost/images/blob/main/games/liteloader-bds/Dockerfile)
- [`games-bdsx`](https://github.com/sparkedhost/images/blob/main/games/bdsx/Dockerfile)
- [`games-source`](https://github.com/sparkedhost/images/blob/main/games/source/Dockerfile)
- [`games-dotnet`](https://github.com/sparkedhost/images/blob/main/games/dotnet/Dockerfile)
- [`games-wine-latest`](https://github.com/sparkedhost/images/blob/main/games/wine-latest/Dockerfile)
- [`games-wine-staging`](https://github.com/sparkedhost/images/blob/main/games/wine-staging/Dockerfile)
- [`games-proton`](https://github.com/sparkedhost/images/blob/main/games/proton/Dockerfile)
- [`games-valheim`](https://github.com/sparkedhost/images/blob/main/games/valheim/Dockerfile)

### Bot Hosting images

- [`bot-bastion`](https://github.com/sparkedhost/images/blob/main/bot/bastion/Dockerfile)
- [`bot-lavalink`](https://github.com/sparkedhost/images/blob/main/bot/lavalink/Dockerfile)
- [`bot-modmail`](https://github.com/sparkedhost/images/blob/main/bot/modmail/Dockerfile)
- [`bot-redbot`](https://github.com/sparkedhost/images/blob/main/bot/redbot/Dockerfile)

### Generic images

- [`generic-debian`](https://github.com/sparkedhost/images/blob/main/generic/debian/Dockerfile)
- [`generic-mono`](https://github.com/sparkedhost/images/blob/main/generic/mono/Dockerfile)
- [`generic-wine`](https://github.com/sparkedhost/images/blob/main/generic/wine/Dockerfile)

### Other images

- [`azul-prime-17`](https://github.com/sparkedhost/images/blob/main/other/azul-prime-17/Dockerfile)
- [`azul-prime-19`](https://github.com/sparkedhost/images/blob/main/other/azul-prime-19/Dockerfile)

## Requesting changes

Docker caches images locally on every machine they're pulled on, even old tags that aren't in use anymore.
This means we have to keep these images as small as possible. That being said, if you find a problem in our
images that is breaking normal functionality, or have a feature request that you believe is only going to
marginally increase the size, please feel free to create an issue or submit a pull request, we're always
open to new ideas.

[^eol]:
    The software inside this image (Java, Node.js, Python...) is considered end of life, meaning official
    support from the developers has now ended and it's recommended to update.

[^antimalware]:
    MCAntiMalware is an anti-malware software for Minecraft servers that detects over 900 malicious plugins.
    This image automatically downloads the latest release, runs a single scan against the working directory
    (/home/container/, where the server files are located) and exits.
    [Read more about this anti-malware here](https://github.com/OpticFusion1/MCAntiMalware).
