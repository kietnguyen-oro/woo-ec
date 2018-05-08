# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  if (/cygwin|mswin|mingw|bccwin|wince|emx/ =~ RUBY_PLATFORM) != nil
    config.vm.synced_folder ".", "/vagrant", mount_options: ["dmode=700,fmode=600"]
  else
    config.vm.synced_folder ".", "/vagrant"
  end

  if Vagrant.has_plugin?("vagrant-cachier")
    config.cache.scope = :box
  end

  if Vagrant.has_plugin?("vagrant-vbguest")
    config.vbguest.auto_update = false
    config.vbguest.no_install = true
    config.vbguest.no_remote = true
  end

  config.vm.box = "bento/centos-6.9"
  config.ssh.insert_key = false

  config.vm.define :ec_basic do |ec_basic|
    ec_basic.vm.network "private_network", ip: "192.168.33.40"
    ec_basic.vm.synced_folder ".", "/vagrant", type: "nfs",
        mount_options: ['rw', 'vers=3', 'tcp'], linux__nfs_options: ['rw', 'no_subtree_check', 'all_squash', 'async']

    ec_basic.vm.provider "virtualbox" do |vb|
      vb.memory = 4096
      vb.cpus = 4
    end

    ec_basic.vm.provision "ansible_local" do |ansible|
      ansible.playbook = "ansible/production.yml"
      ansible.raw_arguments = "-e @ansible/group_vars/local.yml -e @ansible/group_vars/secret.yml"
      ansible.verbose = "vvv"
      ansible.start_at_task = ""
    end
  end
end
