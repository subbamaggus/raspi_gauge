import json
import requests
import csv

print('building data')
data = {}

with open('../data/data.csv') as csvfile:
    spamreader = csv.reader(csvfile, delimiter=',')
    for row in spamreader:
        print(':'.join(row))
    
        data['data'] = ({
            'date': row[0],
            'value': row[1]
        })

print('json: {}'.format(json.dumps(data)))


print('building request')
url = "http://www.ibkerle.de/archiver/?auth=f03ada5ae38129d70e0b3c9992df812c"
headers = {'Content-type': 'application/json', 'Accept': 'text/plain'}


print('sending request')
r = requests.post(url, data=json.dumps(data), headers=headers)


print('request status {}'.format(r.status_code))
print(r.content)