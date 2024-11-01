require 'eth'
message = ARGV[0]
signature = ARGV[1]

#p "0:#{ARGV[0]}"
#p "1:#{ARGV[1]}"
#p "2:#{ARGV[2]}"

public_key = Eth::Signature.personal_recover(message,signature)

address = Eth::Util.public_key_to_address(public_key)
p address
