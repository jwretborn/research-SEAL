import time
import os
import sys
import glob
from mysql_conn import dbWrapper
from datetime import datetime

class patientParser():
	db = dbWrapper()
	hospitals = []
	key_map = {
		'id' 			: 0,
		'in_timestamp' 	: 1,
		'out_timestamp'	: 2,
		'out_reason'	: 3,
		'in_unit'		: 4,
		'out_unit'		: 5,
		'ward'			: 6
	}
	file_path = '/contact.csv'
	exclude = ['HLBARN', 'HLBASSK', 'HLBATRI']

	def read_files(self):
		path = sys.path[0]+"/data/"
		for i in range(0, len(self.hospitals)):
			self.hospitals[i]['patients'] = []
			try :
				for files in os.listdir(path+self.hospitals[i]['signature']) :
					if files.endswith('.csv') and files.startswith('contact') :
						f = open(path+self.hospitals[i]['signature']+"/"+files)
						for line in f:
							self.hospitals[i]['patients'].append(line.split(','))
			except :
				print 'No file found for '+self.hospitals[i]['name']

			print str(len(self.hospitals[i]['patients'])) + " patients found for " + self.hospitals[i]['name']

		return self.hospitals

	def insert_patients(self):
		self.db.table('seal_patients')
		il = [] # Insert list
		ul = [] # Update list

		for i in range(0, len(self.hospitals)):
			for line in self.hospitals[i]['patients']:
				u = {}
				for key in self.key_map.keys():
					u[key] = '_'.join(line[self.key_map[key]].split(' '))
	
				for k in ['in_timestamp', 'out_timestamp'] :
					s = u[k]
					d = datetime(int(s[0:4]), int(s[5:7]), int(s[8:10]), int(s[11:13]), int(s[14:16]), int(s[17:20]))
					u[k] = str(int(time.mktime(d.timetuple())))
	
				u['hospital_id'] = self.hospitals[i]['id']
	
				if u['in_unit'] not in self.exclude :
					self.db.select('*')
					self.db.where("`id` = '"+u['id']+"'")
					res = self.db.query()
					if len(res) > 0 :
						self.db.update(u['id'], u)
					else :
						il.append(u)
			print self.hospitals[i]['name']+": Updates: " + str(len(ul)) + " - Inserts: " + str(len(il))

			self.db.insert(il)

	def fetch_hospitals(self):
		self.db.table('seal_hospitals');
		self.db.select('*');
		self.hospitals = self.db.query(True);


	def run(self):
		self.db.connect()
		self.fetch_hospitals();
		self.read_files()
		self.insert_patients()

parser = patientParser()
parser.run()