import boto3
import time
import xlwt
from xlwt import Workbook
import json
import csv
import sys


s3 = boto3.resource('s3',
                    aws_access_key_id="",
                    aws_secret_access_key='')


def get_kv_relationship(key_map, value_map, block_map):
    kvs = {}
    for _, key_block in key_map.items():
        value_block = find_value_block(key_block, value_map)
        key = get_text(key_block, block_map)
        val = get_text(value_block, block_map)
        kvs[key] = val
    return kvs


def find_value_block(key_block, value_map):
    for relationship in key_block['Relationships']:
        if relationship['Type'] == 'VALUE':
            for value_id in relationship['Ids']:
                value_block = value_map[value_id]
    return value_block


def print_kvs(kvs):
    for key, value in kvs.items():
        print(key, ":", value)


def startJob(s3BucketName, objectName):
    response = None
    client = boto3.client('textract', region_name='us-east-2')
    response = client.start_document_analysis(
        DocumentLocation={
            'S3Object': {
                'Bucket': s3BucketName,
                'Name': objectName
            }
        },
        FeatureTypes=["TABLES", "FORMS"]  # required, accepts TABLES, FORMS
    )
    return response["JobId"]


def generate_table_csv(table_result, blocks_map, table_index):
    rows = get_rows_columns_map(table_result, blocks_map)

    table_id = 'Table_' + str(table_index)
    tablecsv = []
    # get cells.
    csv = 'Table: {0}\n\n'.format(table_id)

    for _, cols in rows.items():
        tablecsv0 = []

        for col_index, text in cols.items():
            csv += '{}'.format(text) + ","
            jsoncsv = {}
            jsoncsv["column"+str(col_index)] = text
            tablecsv0.insert(len(tablecsv0), jsoncsv)
        csv += '\n'
        tablecsv.insert(len(tablecsv), tablecsv0)
    csv += '\n\n\n'
    return tablecsv


def get_rows_columns_map(table_result, blocks_map):
    rows = {}
    for relationship in table_result['Relationships']:
        if relationship['Type'] == 'CHILD':
            for child_id in relationship['Ids']:
                cell = blocks_map[child_id]
                if cell['BlockType'] == 'CELL':
                    row_index = cell['RowIndex']
                    col_index = cell['ColumnIndex']
                    if row_index not in rows:
                        # create new row
                        rows[row_index] = {}

                    # get the text value
                    rows[row_index][col_index] = get_text(cell, blocks_map)
    return rows


def get_text(result, blocks_map):
    text = ''
    if 'Relationships' in result:
        for relationship in result['Relationships']:
            if relationship['Type'] == 'CHILD':
                for child_id in relationship['Ids']:
                    word = blocks_map[child_id]
                    if word['BlockType'] == 'WORD':
                        text += word['Text'] + ' '
                    if word['BlockType'] == 'SELECTION_ELEMENT':
                        if word['SelectionStatus'] == 'SELECTED':
                            text += 'X '
    return text


def isJobComplete(jobId):
    client = boto3.client('textract', region_name='us-east-2')
    status = "IN_PROGRESS"
    while(status == "IN_PROGRESS"):
        time.sleep(5)
        response = client.get_document_analysis(JobId=jobId)
        status = response["JobStatus"]
        #print("Job status: {}".format(status))
    return status


def getJobResults(jobId):
    blocks = []
    client = boto3.client('textract', region_name='us-east-2')
    response = client.get_document_analysis(JobId=jobId)
    blocks.extend(response['Blocks'])
    nextToken = None
    if('NextToken' in response):
        nextToken = response['NextToken']
    while(nextToken):
        response = client.get_document_analysis(JobId=jobId,
                                                NextToken=nextToken)
        blocks.extend(response['Blocks'])
        if('NextToken' in response):
            nextToken = response['NextToken']
        else:
            nextToken = None
    return blocks


def parseDocument(documentName, key):
    try:
        # Document
        s3BucketName = "textract-console-us-east-2-089ddba4-de00-4c3d-9200-b32a6a64b062"
        i = 1
        result = ""
        globalJson = {}
        kvs = ""
        lines = []
        tables = []
        key_map = {}
        value_map = {}
        block_map = {}
        jobId = startJob(s3BucketName, documentName)

        if(isJobComplete(jobId)):
            blocks = getJobResults(jobId)
            file = open(f"{key}.raw.json", "w")
            file.write(json.dumps(blocks, indent=4, sort_keys=True))
            file.close()


        for block in blocks:
            block_id = block['Id']
            block_map[block_id] = block
            if block['BlockType'] == "KEY_VALUE_SET":
                if 'KEY' in block['EntityTypes']:
                    key_map[block_id] = block
                else:
                    value_map[block_id] = block
        kvs = get_kv_relationship(key_map, value_map, block_map)
        globalJson['keyValue'] = kvs
        blocks_map = {}
        table_blocks = []
        for block in blocks:
            blocks_map[block['Id']] = block
            if block['BlockType'] == "TABLE":
                table_blocks.append(block)

        if len(table_blocks) <= 0:
            print("<b> NO Table FOUND </b>")

        for index, table in enumerate(table_blocks):
            tables.insert(len(tables), generate_table_csv(
                table, blocks_map, index + 1))
        # show the results
        for item in blocks:
            if item["BlockType"] == "LINE":
                result = result+'/n'+item["Text"]
                lines.insert(len(lines), item["Text"])
                i = i+1
        globalJson['Lines'] = lines
        globalJson['Tables'] = tables
        file = open(f"{key}.json", "w")
        file.write(json.dumps(globalJson, indent=4, sort_keys=True))
        file.close()
        print("successfully finished executing the script")
    except Exception as e:
            file = open(f"{key}.error.txt", "w")
            file.write(str(e))
            file.close()


def main():
    documentName = sys.argv[1]
    parseDocument(documentName, "key")


if __name__ == "__main__":
    main()
