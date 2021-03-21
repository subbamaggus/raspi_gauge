import json
import requests
import csv

print('building data')

data = {}

with open('data/data.csv') as csvfile:
    spamreader = csv.reader(csvfile, delimiter=',')
    for row in spamreader:
        print(':'.join(row))
    
        data['data'] = []
        data['data'].append({
            'date': row[0],
            'sensor': 'data',
            'value': row[1]
        })

print('data: {}'.format(json.dumps(data)))

print('building request')

url = "http://localhost/archiver.php"
#data = {'sender': 'Alice', 'receiver': 'Bob', 'message': 'We did it!'}
headers = {'Content-type': 'application/json', 'Accept': 'text/plain'}

print('sending request')
r = requests.post(url, data=json.dumps(data), headers=headers)

print('request status {}'.format(r.status_code))
print(r.content)