Vagrant.configure("2") do |config|

  config.vm.box = "bento/ubuntu-16.04"
  config.vm.box_version = "201801.02.0"
  config.vm.box_check_update = false
  config.vm.provision "shell", path: "repo-tools/vagrant/VagrantProvision.sh"

  config.vm.network "forwarded_port", guest: 80, host: 8080

  config.vm.synced_folder "./example", "/var/www/html", create: true

end
