#!/bin/sh

git clone https://github.com/rbenv/rbenv.git ~/.rbenv
git clone https://github.com/rbenv/ruby-build.git ~/.rbenv/plugins/ruby-build
export PATH="~/.rbenv/bin:$PATH"
export RBENV_ROOT=~/.rbenv
eval "$(rbenv init -)"
echo $PATH
echo $RBENV_ROOT
~/.rbenv/bin/rbenv install -l
CONFIGURE_OPTS='--disable-install-rdoc' TMPDIR="${PWD}/tmp" ~/.rbenv/bin/rbenv install 2.6.9
~/.rbenv/bin/rbenv global 2.6.9
which ruby
which gem
#apt-get install libtool
ruby --version
gem --version
gem install eth
GEM_HOME="${HOME}/.gem" gem install eth