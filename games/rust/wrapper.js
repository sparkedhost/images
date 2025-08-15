#!/usr/bin/env node

const fs = require("fs");
const path = require("path");
const { exec } = require("child_process");

const logFilePath = path.join(__dirname, "latest.log");
fs.writeFile(logFilePath, "", (err) => {
    if (err) console.log("Callback error in appendFile:" + err);
});

let startupCmd = "";
const args = process.argv.splice(process.execArgv.length + 2);
for (let i = 0; i < args.length; i++) {
    startupCmd += args[i] + (i === args.length - 1 ? "" : " ");
}

if (startupCmd.length < 1) {
    console.log("Error: Please specify a startup command.");
    process.exit(1);
}

const seenPercentage = {};
function filter(data) {
    const str = data.toString();
    if (str.startsWith("Loading Prefab Bundle ")) {
        const percentage = str.substr("Loading Prefab Bundle ".length);
        if (seenPercentage[percentage]) return;
        seenPercentage[percentage] = true;
    }

    console.log(str);
}

console.log("Starting Rust...");

let exited = false;
const gameProcess = exec(startupCmd);
gameProcess.stdout.on("data", filter);
gameProcess.stderr.on("data", filter);
gameProcess.on("exit", function (code, signal) {
    exited = true;
    if (code) {
        console.log("Main game process exited with code " + code);
    }
});

function initialListener(data) {
    const command = data.toString().trim();
    if (command === "quit") {
        gameProcess.kill("SIGTERM");
    } else {
        console.log(`Unable to run "${command}" due to RCON not being connected yet.`);
    }
}

process.stdin.resume();
process.stdin.setEncoding("utf8");
process.stdin.on("data", initialListener);

process.on("exit", function () {
    if (!exited) {
        console.log("Received request to stop the process, stopping the game...");
        gameProcess.kill("SIGTERM");
    }
});

function poll() {
    if (!process.env.RCON_PORT || !process.env.RCON_PASS) {
        console.error("Missing RCON_PORT or RCON_PASS in environment.");
        process.exit(1);
    }

    const serverHostname = process.env.RCON_IP || "localhost";
    const serverPort = process.env.RCON_PORT;
    const serverPassword = process.env.RCON_PASS;
    const WebSocket = require("ws");

    function createPacket(command) {
        return JSON.stringify({
            Identifier: -1,
            Message: command.trim(),
            Name: "WebRcon",
        });
    }

    const ws = new WebSocket(`ws://${serverHostname}:${serverPort}/${serverPassword}`);
    let waiting = true;

    ws.on("open", () => {
        console.log('Connected to RCON. Please wait until the server status switches to "Running".');
        waiting = false;

        ws.send(createPacket("status"));

        process.stdin.removeListener("data", initialListener);
        gameProcess.stdout.removeListener("data", filter);
        gameProcess.stderr.removeListener("data", filter);

        process.stdin.on("data", function (text) {
            ws.send(createPacket(text));
        });
    });

    ws.on("message", function (data) {
        try {
            const json = JSON.parse(data);
            if (json?.Message) {
                console.log(json.Message);
                fs.appendFile(logFilePath, "\n" + json.Message, (err) => {
                    if (err) console.log("Callback error in appendFile:" + err);
                });
            }
        } catch (e) {
            console.log("Error parsing JSON message:", e);
        }
    });

    ws.on("error", function () {
        waiting = true;
        console.log("Waiting for RCON to come up...");
        setTimeout(poll, 5000);
    });

    ws.on("close", function () {
        if (!waiting) {
            console.log("Connection to server closed.");
            exited = true;
            process.exit();
        }
    });
}

poll();
