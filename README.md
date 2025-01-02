
################# Start custome package use in the your project ###########
# Step1: add this blew line in composer.json after this line ("license": "MIT",)
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/ritikajangirhipl/common-repo.git"
        }
    ],

# Step2: Run thi command for package authenticate.
composer config --global --auth github-oauth.github.com acceess_token

# Step3: run this command
composer require vendor/common-repo

################# End custome package use in the your project  ###########





############## Add css,js and view files ####################
**Step:1 Run this command** 
        php artisan vendor:publish --provider="Vendor\CommonPackage\Providers\CommonServiceProvider" --tag=public
############## Add css,js and view files ####################






#################### Start For AMLBOt ##########################

#  use for AMLBotHttpTrait trait in the Controller 
use Vendor\CommonPackage\Traits\AMLBotHttpTrait;

#  use for AMLBotException trait in the Controller 
use Vendor\CommonPackage\Traits\AMLBotException;

#################### End For AMLBOt ##########################
