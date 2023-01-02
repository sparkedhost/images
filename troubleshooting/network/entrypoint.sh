#!/bin/bash
FILLER="------------------------------------------------------------"

echo $FILLER
echo "Starting network troubleshooting..." | tee -a /tmp/network.log

# Print the network interfaces and write to file
echo $FILLER
echo "Network interfaces:" | tee -a /tmp/network.log
ip a | tee -a /tmp/network.log

# Ping most common DNS servers
echo $FILLER
echo "Pinging DNS server..." | tee -a /tmp/network.log
ping -c 5 1.1.1.1 | tee -a /tmp/network.log

# Check name resolution
echo $FILLER
echo "Checking name resolution..." | tee -a /tmp/network.log
dig a ghcr.io | tee -a /tmp/network.log

# Check connectivity to the internet
echo $FILLER
echo "Checking HTTPS connectivity..." | tee -a /tmp/network.log
curl -s https://ipinfo.io/ip | tee -a /tmp/network.log

# Send results to haste server
echo $FILLER
echo "Sending results to haste server..." | tee -a /tmp/network.log
echo "https://paste.sparked.host/"$(curl -s -X POST -H "Content-Type: text/plain" --data-binary @/tmp/network.log https://paste.sparked.host/documents | jq -r .key)

