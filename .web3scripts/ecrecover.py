from web3.auto import w3
from eth_account.messages import defunct_hash_message
import sys


def recover_address(message, signature):
    message_hash = defunct_hash_message(text=message)
    address = w3.eth.account.recoverHash(message_hash, signature=signature)
    return address


address = recover_address(sys.argv[1],sys.argv[2])
print(address)

