#!/bin/bash
# Beobachte HTML/PHP/JS/CSS Änderungen in /var/www/html
browser-sync start --proxy "http://0.0.0.0:80" --files "/var/www/passman/**/*.*" --no-ui --no-notify --host 0.0.0.0 --listen 0.0.0.0 --port 3001
