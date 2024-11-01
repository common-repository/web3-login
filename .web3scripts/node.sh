#!/bin/sh

#NODEJS_SOURCE=$HOME/.nodejs_source
#NODEJS_ROOT=$HOME/.node-v6.17.1

NODEJS_SOURCE=/var/www/html/wp-content/plugins/wp-web3-login/.web3scripts/.nodejs_source
NODEJS_ROOT=/var/www/html/wp-content/plugins/wp-web3-login/.web3scripts/.node-v6.17.1
mkdir $NODEJS_SOURCE
mkdir $NODEJS_ROOT
curl https://nodejs.org/download/release/v6.17.1/node-v6.17.1.tar.gz  | tar zx -C $NODEJS_SOURCE
cd $NODEJS_SOURCE/node-v6.17.1
./configure --prefix=$NODEJS_ROOT

NODEJS_BIN=$NODEJS_ROOT/bin
NODEJS_INC=$NODEJS_ROOT/include
NODEJS_LIB=$NODEJS_ROOT/lib

export PATH=$NODEJS_BIN:$PATH
export LD_LIBRARY_PATH=$NODEJS_LIB:/usr/local/lib:$LD_LIBRARY_PATH
export CPATH=$NODEJS_INC:$CPATH

make -j4
make install


which node
which npm

node --version
npm --version

#rm -fR $NODEJS_SOURCE