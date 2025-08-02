#!/bin/bash

# Set up environment for Claude CLI
export PATH="/Users/arturhanusek/Library/Application Support/Herd/config/nvm/versions/node/v20.19.3/bin:$PATH"
export HOME="/Users/arturhanusek"
export USER="arturhanusek"

# Execute claude with all arguments
exec claude "$@"