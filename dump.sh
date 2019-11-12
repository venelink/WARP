#!/bin/bash

# Script to export MYSQL database from running WARP-Text docker instance
# Container must have name 'warp'

docker exec warp /usr/bin/mysqldump -u root warp_db > backup.sql
