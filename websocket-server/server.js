require("dotenv").config();
const WebSocket = require("ws");
const mysql = require("mysql2/promise");

const PORT = process.env.PORT || 8080;

// Create DB connection pool
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    waitForConnections: true,
    connectionLimit: 10
});

const wss = new WebSocket.Server({ port: PORT }, () => {
    console.log(`🚀 WebSocket server for chat-function launched on port: ${PORT}`);
});

let clients = {};

wss.on("connection", (ws, req) => {
    let userId = null;

    ws.on("message", async (message) => {
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

            try {
                // Save message to database
                const conn = await pool.getConnection();
                await conn.query(
                    "INSERT INTO messages (sender_id, receiver_id, text, timestamp) VALUES (?, ?, ?, ?)",
                    [senderId, receiverId, text, timestamp]
                );
                conn.release();
                console.log("MSG_LOG: 💾 Message saved!");

                // Send to receiver if online
                if (clients[receiverId]) {
                    clients[receiverId].send(JSON.stringify({
                        type: "message",
                        senderId,
                        text,
                        timestamp,
                        unread: true
                    }));

                    // Send delivery confirmation back to sender
                    if (clients[senderId]) {
                        clients[senderId].send(JSON.stringify({
                            type: "message_delivered",
                            receiverId
                        }));
                    }
                }
                else if (data.type === "message_read") {
                    const { senderId, receiverId } = data;

                    // Update database to mark messages as read
                    try {
                        const conn = await pool.getConnection();
                        await conn.query(
                            "UPDATE messages SET is_read = TRUE WHERE sender_id = ? AND receiver_id = ? AND is_read = FALSE",
                            [senderId, receiverId]
                        );
                        conn.release();

                        if (clients[senderId]) {
                            clients[senderId].send(JSON.stringify({
                                type: "message_read",
                                senderId: receiverId, // This is who read the message
                                receiverId: senderId  // This is who sent the original message
                            }));
                        }
                    } catch (err) {
                        console.error("Error marking messages as read:", err);
                    }
                }
            } catch (err) {
                console.error("Error handling message:", err);
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

// Broadcast new post notifications to followers
async function broadcastNewPost(postId, authorId, username) {
    try {
        // Get followers of the post author
        const conn = await pool.getConnection();
        const [followers] = await conn.query(
            "SELECT follower_id FROM followers WHERE followed_id = ?",
            [authorId]
        );
        conn.release();

        // Send notification to each follower
        followers.forEach(follower => {
            const followerId = follower.follower_id;
            if (clients[followerId]) {
                clients[followerId].send(JSON.stringify({
                    type: "notification",
                    notificationType: "post",
                    postId: postId,
                    username: username
                }));
                console.log(`NOTIFY_LOG: 📣 Notified follower ${followerId} about new post ${postId}`);
            }
        });
    } catch (err) {
        console.error("Error broadcasting post:", err);
    }
}

// Notify post owner about comments/likes
function notifyPostOwner(postOwnerId, interactorId, username, type) {
    if (clients[postOwnerId] && postOwnerId != interactorId) {
        clients[postOwnerId].send(JSON.stringify({
            type: "notification",
            notificationType: type,
            username: username
        }));
        console.log(`NOTIFY_LOG: 📣 Notified post owner ${postOwnerId} about new ${type}`);
    }
}

// REST API endpoint handling
const http = require('http');
const url = require('url');

const server = http.createServer(async (req, res) => {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'POST');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    if (req.method === 'POST') {
        const parsedUrl = url.parse(req.url, true);
        const path = parsedUrl.pathname;

        let body = '';
        req.on('data', chunk => {
            body += chunk.toString();
        });

        req.on('end', async () => {
            try {
                const data = JSON.parse(body);

                if (path === '/notify/post') {
                    await broadcastNewPost(data.postId, data.authorId, data.username);
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: true }));
                }
                else if (path === '/notify/comment') {
                    notifyPostOwner(data.postOwnerId, data.commenterId, data.username, 'comment');
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: true }));
                }
                else if (path === '/notify/like') {
                    notifyPostOwner(data.postOwnerId, data.likerId, data.username, 'like');
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: true }));
                }
                else {
                    res.writeHead(404);
                    res.end();
                }
            } catch (err) {
                console.error('Error processing request:', err);
                res.writeHead(500);
                res.end();
            }
        });
    } else {
        res.writeHead(404);
        res.end();
    }
});

// Start HTTP server on a different port
const HTTP_PORT = process.env.HTTP_PORT || 8081;
server.listen(HTTP_PORT, () => {
    console.log(`🚀 HTTP server for notifications running on port: ${HTTP_PORT}`);
});

// Export functions for external use
module.exports = {
    broadcastNewPost,
    notifyPostOwner,
    getClients: () => clients
};