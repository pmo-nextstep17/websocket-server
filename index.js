const express = require("express");
const http = require("http");
const WebSocket = require("ws");

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

wss.on("connection", (ws) => {
    console.log("Nuovo client connesso!");

    ws.on("message", (message) => {
        console.log("Messaggio ricevuto:", message.toString());
        ws.send("Ricevuto: " + message.toString());
    });

    ws.on("close", () => console.log("Client disconnesso"));
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => console.log(`WebSocket Server in ascolto su porta ${PORT}`));
