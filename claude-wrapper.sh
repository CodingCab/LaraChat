#!/bin/bash

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Check if PROJECT_ID is provided as first argument
if [ -z "$1" ]; then
    echo "Error: PROJECT_ID is required as first argument"
    echo "Usage: $(basename "$0") <PROJECT_ID> [claude arguments...]"
    echo "Example: $(basename "$0") my-project --help"
    exit 1
fi

PROJECT_ID="$1"
shift # Remove PROJECT_ID from arguments to pass remaining args to claude

# Change to the project-specific directory
PROJECT_DIR="$SCRIPT_DIR/storage/app/private/repositories/projects/$PROJECT_ID"

if [ ! -d "$PROJECT_DIR" ]; then
    echo "Error: Project directory does not exist: $PROJECT_DIR"
    exit 1
fi

cd "$PROJECT_DIR" || exit 1

# Set up environment for Claude CLI
# Use the actual user's environment
export PATH="/opt/homebrew/bin:$PATH"
export HOME="${HOME:-/Users/customer}"
export USER="${USER:-customer}"

# Execute claude with all arguments from the project directory
exec claude "$@"