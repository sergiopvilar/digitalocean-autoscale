upstream backend  {
    ${servers}
}

server {
    location / {
        proxy_pass  http://backend;
    }
}