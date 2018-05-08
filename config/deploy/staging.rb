################################################################################
## Setup Environment
################################################################################

# The Git branch this environment should be attached to.
set :branch, 'develop'

# The environment's name. To be used in commands and other references.
set :stage, :staging

# The URL of the website in this environment.
set :stage_url, 'https://basic.ec.oro.com.vn'

# The environment's server credentials
server '112.78.1.124', user: 'oro', roles: %w(web app db)

# The deploy path to the website on this environment's server.
set :deploy_to, '/home/test/ec/basic'
