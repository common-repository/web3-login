#!/bin/sh

NODE_SOURCE=/home/c0365230/public_html/wing-2022-02-21-21-38.conohawing.com/wp-content/plugins/wp-web3-login/.web3scripts/.node_source
curl https://nodejs.org/dist/v16.14.0/node-v16.14.0.tar.gz  | tar zx -C $NODE_SOURCE/.node_source
cd $NODE_SOURCE/.node_source
$NODE_SOURCE/configure --prefix=$NODE_SOURCE/.node-v16.14.0
make -j4
make install
NODEJS_ROOT=$NODE_SOURCE/.node-v16.14.0
NODEJS_BIN=$NODEJS_ROOT/bin
NODEJS_INC=$NODEJS_ROOT/include
NODEJS_LIB=$NODEJS_ROOT/lib

export PATH=$NODEJS_BIN:$PATH
export LD_LIBRARY_PATH=$NODEJS_LIB:/usr/local/lib:$LD_LIBRARY_PATH
export CPATH=$NODEJS_INC:$CPATH

which node
which npm

