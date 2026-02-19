const express = require('express');
const { createServer } = require('http');
const { Server } = require('socket.io');
const cookieParser = require('cookie-parser');

const app = express();
const httpServer = createServer(app);

// Configure Socket.IO with CORS
const io = new Server(httpServer, {
  cors: {
    origin: process.env.CORS_ORIGIN || "http://localhost:8080",
    credentials: true,
    methods: ["GET", "POST"]
  },
  transports: ['websocket', 'polling']
});

app.use(cookieParser());
app.use(express.json());

// Store user socket connections
const userSockets = new Map(); // userId -> Set of socket IDs

// Simple health check endpoint
app.get('/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    connections: io.engine.clientsCount,
    users: userSockets.size
  });
});

// Endpoint for backend to emit notifications
app.post('/notify', (req, res) => {
  const { userId, notification } = req.body;

  if (!userId || !notification) {
    return res.status(400).json({ error: 'userId and notification required' });
  }

  // Emit to all sockets connected for this user
  const sockets = userSockets.get(parseInt(userId));
  if (sockets && sockets.size > 0) {
    sockets.forEach(socketId => {
      io.to(socketId).emit('notification', notification);
    });
    res.json({ success: true, delivered: true, socketCount: sockets.size });
  } else {
    res.json({ success: true, delivered: false, message: 'User not connected' });
  }
});

// Endpoint for backend to emit notification count updates
app.post('/notify-count', (req, res) => {
  const { userId, count } = req.body;

  if (!userId || count === undefined) {
    return res.status(400).json({ error: 'userId and count required' });
  }

  const sockets = userSockets.get(parseInt(userId));
  if (sockets && sockets.size > 0) {
    sockets.forEach(socketId => {
      io.to(socketId).emit('notificationCount', { count });
    });
    res.json({ success: true, delivered: true });
  } else {
    res.json({ success: true, delivered: false });
  }
});

// Socket.IO connection handling
io.on('connection', (socket) => {
  console.log(`[Socket] New connection: ${socket.id}`);

  // Authenticate user from handshake query
  socket.on('authenticate', (data) => {
    const userId = parseInt(data.userId);
    
    if (!userId || isNaN(userId)) {
      console.log(`[Socket] Invalid authentication for ${socket.id}`);
      socket.emit('authError', { message: 'Invalid user ID' });
      return;
    }

    // Store the user ID on the socket
    socket.userId = userId;

    // Add socket to user's connections
    if (!userSockets.has(userId)) {
      userSockets.set(userId, new Set());
    }
    userSockets.get(userId).add(socket.id);

    console.log(`[Socket] User ${userId} authenticated on socket ${socket.id}`);
    socket.emit('authenticated', { userId, socketId: socket.id });

    // Join a room for this user
    socket.join(`user:${userId}`);
  });

  // Handle disconnection
  socket.on('disconnect', () => {
    console.log(`[Socket] Disconnected: ${socket.id}`);
    
    if (socket.userId) {
      const sockets = userSockets.get(socket.userId);
      if (sockets) {
        sockets.delete(socket.id);
        if (sockets.size === 0) {
          userSockets.delete(socket.userId);
        }
      }
    }
  });

  // Ping/pong for keep-alive
  socket.on('ping', () => {
    socket.emit('pong');
  });
});

const PORT = process.env.PORT || 3000;

httpServer.listen(PORT, () => {
  console.log(`[WebSocket] Server listening on port ${PORT}`);
  console.log(`[WebSocket] CORS origin: ${process.env.CORS_ORIGIN || "http://localhost:8080"}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('[WebSocket] SIGTERM received, closing server...');
  httpServer.close(() => {
    console.log('[WebSocket] Server closed');
    process.exit(0);
  });
});
