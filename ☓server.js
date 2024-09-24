const express = require('express');
const cors = require('cors');
const { createProxyMiddleware } = require('http-proxy-middleware');

const app = express();

app.use(cors());

app.use('/api', createProxyMiddleware({
    target: 'https://api.dify.ai',
    changeOrigin: true,
    pathRewrite: {
        '^/api': '/v1',
    },
}));

app.listen(3000, () => {
    console.log('Proxy server is running on port 3000');
});
