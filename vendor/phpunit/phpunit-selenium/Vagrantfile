VAGRANTFILE_API_VERSION = "2"

$setupEnvironment = <<-SCRIPT

cd /vagrant

source ./.ci/vagrant_pre_setup.sh

source ./.ci/common_env.sh
source ./.ci/vagrant_env.sh

source ./.ci/setup.sh

source ./.ci/start.sh

SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "hashicorp/precise32"

  config.vm.provision "shell", inline: $setupEnvironment
end
