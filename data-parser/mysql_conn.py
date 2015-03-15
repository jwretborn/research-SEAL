import MySQLdb
import time
import os
import sys

class dbWrapper():
	conn_host =		"localhost"
	conn_user =		"root"
	conn_passwd =	"morotspajp"
	conn_db =		"seal"
	conn = 			False
	c =				False
	res = 			False

	_ar_table =		False
	_ar_select =	False
	_ar_where =		False

	def connect(self):
		self.db = MySQLdb.connect(host=self.conn_host, user=self.conn_user,passwd=self.conn_passwd,db=self.conn_db);
		self.c = self.db.cursor()

	def table(self, table=False):
		if not table :
			table = self._ar_table

		self._ar_table = table
		return self

	def select(self, sel='*'):
		self._ar_select = sel
		return self

	def where(self, where=''):
		self._ar_where = where
		return self

	def result(self):
		return self.res

	def query(self, desc=False):
		q = self._make_select()
		if not q :
			return False
		self.c.execute(q)
		self.res = self.c.fetchall()

		if desc :
			q = self._make_describe();
			if q :
				self.c.execute(q)
				res = self.c.fetchall()
				result = []
				for i in range(0, len(self.res)):
					row = {}
					for j in range(0, len(res)):
						row[res[j][0]] = self.res[i][j]
					result.append(row)
	
				return result

		return self.res

	def insert(self, insert):
		self._ar_insert = insert
		if type(self._ar_insert) is dict :
			self._ar_insert = [self._ar_insert]
		
		while len(self._ar_insert) > 0 :
			q = self._make_insert()
			if q :
				self.c.execute(q)
		
		self.db.commit()
		return self

	def update(self, id, data):
		self._ar_where = "`id` = '%s'" % id
		q = self._make_update(data)
		if not q :
			return False
		self.c.execute(q)
		self.db.commit()
		return self

	def delete(self):
		q = self._make_delete()
		if not q :
			return False
		self.c.execute(q)
		self.db.commit()
		return self

	def _make_select(self):
		if not self._ar_select:
			print "You have to make a selection"
			return False
		if not self._ar_table:
			print "You have to set a table"
			return False

		qstring = """SELECT %s FROM %s""" % (self._ar_select, self._ar_table)

		if self._ar_where:
			qstring = ''.join([qstring, """ WHERE %s""" % (self._ar_where)])

		return qstring

	def _make_describe(self):
		if not self._ar_table:
			print "You have to set a table"
			return False

		qstring = """SHOW COLUMNS FROM %s""" % (self._ar_table)

		return qstring

	def _make_insert(self):
		ins = self._ar_insert.pop()
		for k, v in ins.items():
			ins[k] = "'"+str(v)+"'"

		qstring = """INSERT INTO %s (%s) VALUES(%s)""" % (self._ar_table, ", ".join(ins.keys()), ', '.join(ins.values()))

		return qstring

	def _make_delete(self):
		qstring = """DELETE FROM %s WHERE %s""" % (self._ar_table, self._ar_where)

		return qstring

	def _make_update(self, update):
		self._ar_update = ''
		l = []
		for k, v in update.items():
			l.append("`%s` = '%s'" % (k, v))
		self._ar_update = ', '.join(l)
		qstring = """UPDATE %s SET %s WHERE %s""" % (self._ar_table, self._ar_update, self._ar_where)
		return qstring

	def _reset_ar(self):
		self._ar_select = False
		self._ar_table = False
		self._ar_where = False