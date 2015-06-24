import MySQLdb
import time
import os
import sys
from mysql_conn import dbWrapper

class formParser():
	db = dbWrapper()
	lines = []
	update = []
	files = ['/data/example_form.txt']

	def read_file(self, fpath):
		path = sys.path[0]
		f = open(sys.path[0]+fpath)
		for counter, line in enumerate(f):
			if counter > 0: #Skip description row
				self.lines.append(line.strip().split(' '))

		return self.lines

	def update_forms(self):
		self.db.table('seal_forms')

		for line in self.lines:
			u = {}
			if line[1] != '-' :
				u['question_1'] = line[1]
			if line[2] != '-' :
				u['question_2'] = line[2]
			if line[3] != '-' :
				u['type'] = line[3]
			if len(u) > 0 :
				self.db.update(line[0], u)

		self.lines = []

	def insert_forms(self):
		self.db.table('seal_forms')

		for line in self.lines:
			u = {}
			if line[1] != '-' :
				u['question_1'] = line[1]
			if line[2] != '-' :
				u['question_2'] = line[2]
			if line[3] != '-' :
				u['type'] = line[3]
			if len(u) > 0 :
				u['reading_id'] = line[0]
				self.db.insert(u)

		self.lines = []

	def run(self):
		self.db.connect()

		for f in self.files:
			self.read_file(f)
			self.insert_forms()

		#self.db.insert({'reading_id' : '1', 'type' : '1', 'question_1' : '1', 'question_2' : '2', 'created' : '12345', 'object_id' : '1'})
		#self.db.delete()


parser = formParser()
parser.run()