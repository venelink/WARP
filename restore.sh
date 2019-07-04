#!/bin/bash

# Script to restore mysql dump
# Container must have name 'warp'
cat backup.sql | docker exec -i warp /usr/bin/mysql -u root warp_db
