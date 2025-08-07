#!/bin/bash

# Step 1: Navigate to the Laravel project root
cd ..

# Step 2: Create a new Artisan command
php artisan make:command GenerateBashScript

# Check if the command was created successfully
if [ $? -ne 0 ]; then
  echo "Failed to create the Artisan command."
  exit 1
fi

# Step 3: Modify the handle method of the new command
sed -i '/public function handle()/,/}/c\
    public function handle() {\
        $bashScriptContent = "\"#!/bin/bash\\n\\n# Step 1: Navigation\\ncd ..\\n\\n# Step 2: Create a new Laravel controller\\nphp artisan make:controller SampleController\\n\\n# Step 3: Update .env file\\necho \"APP_NAME=NewAppName\" >> .env\\n\\n# Step 4: Create a new route\\necho \"Route::get(\'/sample\', \'SampleController@index\');\" >> routes/web.php\\n\";\
        \
        file_put_contents(\"./storage/sample_bash_script.sh\", $bashScriptContent);\
        $this->info(\"Bash script created successfully!\");\
    }' app/Console/Commands/GenerateBashScript.php

# Step 4: Inform the user
echo "Artisan command 'GenerateBashScript' has been created and configured."