"use strict";

/**
 * Example JavaScript code that interacts with the page and Web3 wallets
 */

 // Unpkg imports
const Web3Modal = window.Web3Modal.default;
const WalletConnectProvider = window.WalletConnectProvider.default;
//const EvmChains = window.EvmChains;
const evmChains = window.evmChains;

const Torus = window.Torus;
//const Fortmatic = window.Fortmatic;

// Web3modal instance
let web3Modal

// Chosen wallet provider given by the dialog window
let provider;


// Address of the selected account
let selectedAccount;

async function metamask_exec() {
  const provider = await detectEthereumProvider()

  //なんかウォレットインストールされてるか調べる
  if (provider) {
	  console.log('(1) installed');
	  if (provider !== window.ethereum) {
		  console.error('Do you have multiple wallets installed?');
	  }
  } else {
	  console.log('(1) not installed');
  }

  //メタマスクがインストールされてるか調べる
  if (window.ethereum && window.ethereum.isMetaMask) {
	  console.log('(2) MetaMask installed');
  }else{
	  console.log('(2) MetaMask not installed');
  }


  /**********************************************************/
  /* Handle chain (network) and chainChanged (per EIP-1193) */
  /**********************************************************/

  const chainId = await ethereum.request({ method: 'eth_chainId' });
  handleChainChanged(chainId);

  ethereum.on('chainChanged', handleChainChanged);

  function handleChainChanged(_chainId) {
	  // 基本的にリロードしろとのこと
	  var lastChainId = localStorage.getItem('lastChainId');//なんでか延々とここ呼ばれるのでリロード抑制したい
	  console.log(_chainId);
	  if (lastChainId != _chainId){
		  localStorage.setItem('lastChainId', _chainId);
		  //ネットワークが切り替えられたらページをリロードする
		  window.location.reload();
	  }
  }

  /***********************************************************/
  /* Handle user accounts and accountsChanged (per EIP-1193) */
  /***********************************************************/

  //let currentAccount = null;
  ethereum
  .request({ method: 'eth_accounts' })
  .then(handleAccountsChanged)
  .catch((err) => {
	  // Some unexpected error.
	  // For backwards compatibility reasons, if no accounts are available,
	  // eth_accounts will return an empty array.
	  console.error(err);
  });

  // Note that this event is emitted on page load.
  // If the array of accounts is non-empty, you're already
  // connected.
  ethereum.on('accountsChanged', handleAccountsChanged);

  // For now, 'eth_accounts' will continue to always return an array
  function handleAccountsChanged(accounts) {
	  if (accounts.length === 0) {
		  // MetaMask is locked or the user has not connected any accounts
		  console.log('Please connect to MetaMask.');
	  } else{
		  //アカウント変わったらリロードするようにしてみる
		  var lastAccount = localStorage.getItem('lastAccount');
		  console.log(accounts[0]);
		  if (accounts[0] !== lastAccount) {
			  localStorage.setItem('lastAccount', accounts[0]);
			  window.location.reload();
		  }
		  // Do any other work!
	  }
  }

  /*********************************************/
  /* Access the user's accounts (per EIP-1102) */
  /*********************************************/

  // 勝手にウォレット繋げないでボタンとか押させなさい とのこと
  document.getElementById('connectButton').onclick = function(){
	  //ボタン押したらウォレット接続
	  ethereum
		  .request({ method: 'eth_requestAccounts' })
		  .then(handleAccountsChanged)
		  .catch((err) => {
		  if (err.code === 4001) {
			  // EIP-1193 userRejectedRequest error
			  // If this happens, the user rejected the connection request.
			  console.log('Please connect to MetaMask.');
		  } else {
			  console.error(err);
		  }
	  });
  };
}
//metamask_exec();


/**
 * Setup the orchestra
 */
function init() {

  console.log("Initializing example");
  console.log("WalletConnectProvider is", WalletConnectProvider);
  console.log("Torus is", Torus);
  //console.log("Fortmatic is", Fortmatic);

  // Tell Web3modal what providers we have available.
  // Built-in web browser provider (only one can exist as a time)
  // like MetaMask, Brave or Opera is added automatically by Web3modal
  const providerOptions = {
	walletconnect: {
	  package: WalletConnectProvider,
	  options: {
		infuraId: "a7948461164045d2947ea4fecca5d9e5",
	  }
	}

	,torus: {
	  package: Torus,
	  options: {
	  }
	}
	
	/*
	,fortmatic: {
	  package: Fortmatic,
	  options: {
		// Mikko's TESTNET api key
		key: "pk_test_391E26A3B43A3350"
	  }
	}
	*/
  };

  web3Modal = new Web3Modal({
	network: "mainnet", // optional
	cacheProvider: true, // optional
	providerOptions // required
	/*    
	cacheProvider: false, // optional
	providerOptions, // required
	*/
  });

}

function EncodeHTMLForm( data )
{
    var params = [];

    for( var name in data )
    {
        var value = data[ name ];
        var param = encodeURIComponent( name ) + '=' + encodeURIComponent( value );

        params.push( param );
    }

    return params.join( '&' ).replace( /%20/g, '+' );
}

/**
 * Kick in the UI action after Web3modal dialog has chosen a provider
 */
async function fetchAccountData() {

  // Get a Web3 instance for the wallet
  const web3 = new Web3(provider);

  console.log("Web3 instance is", web3);

  // Get connected chain id from Ethereum node
  const chainId = await web3.eth.getChainId();
  // Load chain information over an HTTP API
  console.log(evmChains);
  const chainData = await evmChains.getChain(chainId);
  document.querySelector("#network-name").textContent = chainData.name;

  // Get list of accounts of the connected wallet
  const accounts = await web3.eth.getAccounts();

  // MetaMask does not give you all accounts, only the selected account
  console.log("Got accounts", accounts);
  selectedAccount = accounts[0];

  document.querySelector("#selected-account").textContent = selectedAccount;

  // Get a handl
  const template = document.querySelector("#template-balance");
  const accountContainer = document.querySelector("#accounts");

  // Purge UI elements any previously loaded accounts
  accountContainer.innerHTML = '';

  // Go through all accounts and get their ETH balance
  const rowResolvers = accounts.map(async (address) => {
	const balance = await web3.eth.getBalance(address);
	// ethBalance is a BigNumber instance
	// https://github.com/indutny/bn.js/
	const ethBalance = web3.utils.fromWei(balance, "ether");
	const humanFriendlyBalance = parseFloat(ethBalance).toFixed(4);
	// Fill in the templated row and put in the document
	const clone = template.content.cloneNode(true);
	clone.querySelector(".address").textContent = address;
	clone.querySelector(".balance").textContent = humanFriendlyBalance;
	accountContainer.appendChild(clone);
	console.log("web3.eth.personal");
	console.log(web3.eth.personal);
	submitXMLHttpRequest(web3,address);
  });

  // Because rendering account does its own RPC commucation
  // with Ethereum node, we do not want to display any results
  // until data for all accounts is loaded
  await Promise.all(rowResolvers);

  // Display fully loaded UI for wallet data
  document.querySelector("#prepare").style.display = "none";
  document.querySelector("#connected").style.display = "block";
}






/**
 * Fetch account data for UI when
 * - User switches accounts in wallet
 * - User switches networks in wallet
 * - User connects wallet initially
 */
async function refreshAccountData() {

  // If any current data is displayed when
  // the user is switching acounts in the wallet
  // immediate hide this data
  document.querySelector("#connected").style.display = "none";
  //document.querySelector("#prepare").style.display = "block";

  // Disable button while UI is loading.
  // fetchAccountData() will take a while as it communicates
  // with Ethereum node via JSON-RPC and loads chain data
  // over an API call.
  document.querySelector("#btn-connect").setAttribute("disabled", "disabled")
  await fetchAccountData(provider);
  document.querySelector("#btn-connect").removeAttribute("disabled");
  
}


/**
 * Connect wallet button pressed.
 */
async function onConnect() {

  console.log("Opening a dialog", web3Modal);
  try {
	provider = await web3Modal.connect();
  } catch(e) {
	console.log("Could not get a wallet connection", e);
	return;
  }

  // Subscribe to accounts change
  provider.on("accountsChanged", (accounts) => {
	fetchAccountData();
  });

  // Subscribe to chainId change
  provider.on("chainChanged", (chainId) => {
	fetchAccountData();
  });

  // Subscribe to networkId change
  provider.on("networkChanged", (networkId) => {
	fetchAccountData();
  });

  await refreshAccountData();
}

/**
 * Disconnect wallet button pressed.
 */
async function onDisconnect() {

  console.log("Killing the wallet connection", provider);

  // TODO: Which providers have close method?
  if(provider.close) {
	await provider.close();

	// If the cached provider is not cleared,
	// WalletConnect will default to the existing session
	// and does not allow to re-scan the QR code with a new wallet.
	// Depending on your use case you may want or want not his behavir.
	await web3Modal.clearCachedProvider();
	provider = null;
  }

  selectedAccount = null;

  // Set the UI back to the initial state
  document.querySelector("#prepare").style.display = "block";
  document.querySelector("#connected").style.display = "none";
}


/**
 * Main entry point.
 */
window.addEventListener('load', async () => {
  init();
  document.querySelector("#btn-connect").addEventListener("click", onConnect);
  document.querySelector("#btn-disconnect").addEventListener("click", onDisconnect);
});