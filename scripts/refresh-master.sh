#!/bin/bash

# Function to find npm
find_npm() {
    # Check if npm is already in PATH
    if command -v npm >/dev/null 2>&1; then
        echo "npm"
        return 0
    fi
    
    # Check common npm installation locations
    local npm_paths=(
        "/Users/customer/Library/Application Support/Herd/config/nvm/versions/node/v22.17.1/bin/npm"
        "/usr/local/bin/npm"
        "/opt/homebrew/bin/npm"
        "$HOME/.nvm/versions/node/default/bin/npm"
        "$HOME/.volta/bin/npm"
        "$HOME/.fnm/aliases/default/bin/npm"
    )
    
    # Check NVM dynamic path if NVM_DIR exists
    if [ -n "$NVM_DIR" ] && [ -d "$NVM_DIR/versions/node" ]; then
        local latest_node=$(ls -t "$NVM_DIR/versions/node" 2>/dev/null | head -1)
        if [ -n "$latest_node" ]; then
            npm_paths+=("$NVM_DIR/versions/node/$latest_node/bin/npm")
        fi
    fi
    
    # Check each path
    for npm_path in "${npm_paths[@]}"; do
        if [ -x "$npm_path" ]; then
            echo "$npm_path"
            return 0
        fi
    done
    
    return 1
}

# Try to source NVM if available
if [ -f "$HOME/.nvm/nvm.sh" ]; then
    source "$HOME/.nvm/nvm.sh" 2>/dev/null
elif [ -f "/Users/customer/Library/Application Support/Herd/config/nvm/nvm.sh" ]; then
    source "/Users/customer/Library/Application Support/Herd/config/nvm/nvm.sh" 2>/dev/null
fi

# Find npm
NPM_CMD=$(find_npm)

if [ -z "$NPM_CMD" ]; then
    echo "Error: npm not found. Please ensure Node.js and npm are installed."
    echo "Checked common installation locations including:"
    echo "  - System PATH"
    echo "  - Herd NVM installation"
    echo "  - Homebrew (/usr/local/bin and /opt/homebrew/bin)"
    echo "  - NVM, Volta, and FNM installations"
    exit 1
fi

echo "Using npm from: $NPM_CMD"

# Refresh master branch with latest changes and rebuild
git checkout master && git reset --hard HEAD && git pull origin master && "$NPM_CMD" run build && rm -rf public/hot