# Research-SEAL
Research project SEAL, ED crowding

This is the data and parser-code used to analyze information for the research paper 
"Skåne Emergency Department Assessment of Patient Load (SEAL) - a Model to Estimate 
Crowding based on Workload in Swedish Emergency Departments"

# Requirements
To view the raw data you ned a mysql-database which is free and open source and available at http://www.mysql.com/. 
To run the parsers att calculate the variables used in the analysis you need php and a web server (like apache) which are
both open source.
All statistical analysis are done in SPSS.

# Data
The data from the exported .csv files have been parsed and stored here in as mysql-database.

# Files
The /app directory is a javascript frontend framework used to visualize the data and generate reports.
The /api directory is the backend php framework used to analyze and parse the data as well as generate timepoints and 
print questionnaries
The /data directory store the mysql-dump data with all the raw data as well as analyzed data
