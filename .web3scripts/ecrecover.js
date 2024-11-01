const { ethers } = require('ethers');


let msg = process.argv[2];
let signed = process.argv[3];

let address= ethers.utils.verifyMessage(msg, signed);
console.log(address);

