#!/bin/bash
set -e
psql -U oscar oscar_dev -f /opt/oscar-install.sql
