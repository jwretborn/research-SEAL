import time
import os
import sys
from mysql_conn import dbWrapper
from datetime import datetime

class eventParser():
	db = dbWrapper()
	key_map = {
		'patient_id'	: 0,
		'timestamp' 	: 1,
		'hospital_id' 	: 3,
		'activity' 		: 4,
		'unit'			: 5,
		'priority' 		: 6,
		'process' 		: 7,
		'room' 			: 8,
		'doc'			: 9,
		'nurse'			: 10
	}
	hospitals = []
	file_path = '/event.csv'
	#exclude = ['HLBARN', 'HLBASSK', 'HLBATRI']
	exclude = []

	def read_file(self):
		if (len(self.hospitals) < 1) :
			self.fetch_hospitals()

		path = sys.path[0]+"/data/"
		for i in range(0, len(self.hospitals)):
			self.hospitals[i]['events'] = []
			try :
				for files in os.listdir(path+self.hospitals[i]['signature']) :
					if files.endswith('.csv') and files.startswith('event') :
						f = open(path+self.hospitals[i]['signature']+"/"+files)
						for line in f:
							self.hospitals[i]['events'].append(line.split(','))
			except :
				print 'No file found for '+self.hospitals[i]['name']

			print str(len(self.hospitals[i]['events'])) + " events found for " + self.hospitals[i]['name']

		return self.hospitals

	def insert_events(self):
		self.db.table('seal_events')

		# Lets blow everything away!
		self.db.where("id")
		self.db.delete()

		for i in range(0, len(self.hospitals)) :
			l = []
			for line in self.hospitals[i]['events']:
				u = {}
				for key in self.key_map.keys() :
					if len(line) > self.key_map[key] and line[self.key_map[key]] :
						u[key] = '_'.join(line[self.key_map[key]].split(' '))
	
				for k in ['timestamp'] :
					s = u[k]
					d = datetime(int(s[0:4]), int(s[5:7]), int(s[8:10]), int(s[11:13]), int(s[14:16]), int(s[17:20]))
					u[k] = str(int(time.mktime(d.timetuple())))
	
				u['hospital_id'] = self.hospitals[i]['id']
	
				if u['unit'] not in self.exclude :
					l.append(u)

			print self.hospitals[i]['name']+": Inserted "+str(len(l))+" events"

			self.db.insert(l)

	def fetch_hospitals(self):
		self.db.table('seal_hospitals');
		self.db.select('*');
		self.hospitals = self.db.query(True);

	def run(self):
		self.db.connect()
		self.read_file()
		self.insert_events()

parser = eventParser()
parser.run()