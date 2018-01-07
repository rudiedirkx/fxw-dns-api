# Call server, use response code, ignore body
RSP=$(curl -s -o /dev/null -w "%{http_code}" --user user:p@ss --data 'add&domain=example.com&type=txt&name=foo&value=bar&ttl=300' https://fake.fxw.api/)
echo $RSP

# Call server, use full headers and body
RSP=$(curl -sD - --user user:p@ss --data 'add&domain=example.com&type=txt&name=foo&value=bar&ttl=300' https://fake.fxw.api/)
echo $RSP
