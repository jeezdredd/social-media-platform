require("dotenv").config();
const WebSocket = require("ws");
const mysql = require("mysql2");

const PORT = process.env.PORT || 8080;

// DB Conn
const db = mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
});

db.connect(err => {
    if (err) {
        console.error("Error connecting to database:", err);
        process.exit(1);
    }
    console.log("DB_LOG: ✅ Successfully connected to the database!");
});


const wss = new WebSocket.Server({ port: PORT }, () => {
    console.log(`SERVER_LOG: 🚀 WebSocket launched. Port: ${PORT}`);
});

let clients = {};

wss.on("connection", (ws, req) => {
    let userId = null;

    ws.on("message", message => {
        let data;
        try {
            data = JSON.parse(message);
        } catch (e) {
            return console.error("Error parsing JSON:", e);
        }

        if (data.type === "connect") {
            userId = data.userId;
            clients[userId] = ws;
            console.log(`CONN_LOG: 🔵 User ${userId} connected`);
        }

        if (data.type === "message") {
            const { senderId, receiverId, text } = data;
            const timestamp = new Date().toISOString().slice(0, 19).replace("T", " ");

            // Message save to database
            db.query("INSERT INTO messages (sender_id, receiver_id, text, timestamp) VALUES (?, ?, ?, ?)",
                [senderId, receiverId, text, timestamp], (err) => {
                    if (err) {
                        console.error("Error saving message to database:", err);
                        return;
                    }
                    console.log("MSG_LOG: 💾 Message saved!");
                });

            //Sending message to user if online
            if (clients[receiverId]) {
                clients[receiverId].send(JSON.stringify({
                    type: "message",
                    senderId,
                    text,
                    timestamp
                }));
            }
        }
    });

    ws.on("close", () => {
        if (userId) {
            console.log(`CONN_LOG: 🔴 User ${userId} disconnected`);
            delete clients[userId];
        }
    });
});
