#!/bin/bash

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Change to the project directory
cd "$SCRIPT_DIR" || exit 1

# Set up environment for Claude CLI
export PATH="/Users/arturhanusek/Library/Application Support/Herd/config/nvm/versions/node/v22.17.1/bin:$PATH"
export HOME="/Users/arturhanusek"
export USER="arturhanusek"

# Execute claude with all arguments from the project directory
exec claude "$@"