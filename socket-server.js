import express from "express";

import { createServer } from "http";
import { Server } from "socket.io";
import axios from "axios";

const app = express();

const server = createServer(app);
const io = new Server(server, {
    cors: {
        origin: [
            "http://127.0.0.1:8000",
            "http://localhost:8000",
            "http://127.0.0.1:5500",
            "http://localhost:5500",
        ],
    },
});

io.on("connection", (socket) => {
    console.log("Client connected");

    socket.on("chat:send", (message) => {
        console.log(message);
        io.emit("chat:receive", message);
    });

    socket.on("disconnect", () => {
        console.log("Client disconnected");
    });
});

const port = process.env.PORT || 3000;
server.listen(port, () => {
    console.log(`Scoket server running at http://localhost:${port}`);
});
