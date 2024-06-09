import json
import os
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import shortuuid
import script
import threading

# Globals
app = FastAPI()
toDeliverFile = {} # stores key and file path
toDeliverThread = {} # stores key and thread


# Models
class Item(BaseModel):
    filePath: str


# Functions
# Create a thread to run the script for the request made
def runScript(filePath, key):
    thr = threading.Thread(target=script.parseDocument, args=(filePath,key,), kwargs={})
    thr.start()
    return thr


# Endpoints
# Send the name of the file in the body field filePath
@app.post("/recognize")
async def postPath(item: Item):
    # if item.filePath in toDeliverFile.values():
    #         raise HTTPException(status_code=400, detail="File already being processed")

    newKey = shortuuid.uuid()
    toDeliverFile[newKey] = item.filePath
    toDeliverThread[newKey] = runScript(item.filePath, newKey)

    return {"status": "success", "filePath": item.filePath, "key": newKey}

# Get the status - Completed / In Progress / Script failed
@app.get("/status/{itemId}")
async def getStatus(itemId: str):
    if itemId not in toDeliverFile:
        raise HTTPException(status_code=400, detail="Item does not exist")
    if ((itemId in toDeliverFile) and (os.path.exists(f'{itemId}.json'))):
        with open(f'{itemId}.json') as jsonFile:
            data = json.load(jsonFile)
            return {"status": "Completed", "data": data}
    elif ((itemId in toDeliverThread) and toDeliverThread[itemId].is_alive()):
        return {"status": "In Progress"}
    else:
        del toDeliverFile[itemId]
        del toDeliverThread[itemId] 
        try:
            # If the script failed, delete the keys to this file
            file = open(f'{itemId}.error.txt', "r")  
        except:
            raise HTTPException(status_code=400, detail="Error in the script, can't read error log") 
        err = file.read()
        raise HTTPException(status_code=400, detail=f"Error processing file: script exited with message: {str(err)}")   

# Delete the file that was created and the reference
@app.delete("/acknowledged/{itemId}")
async def deleteRef(itemId: str):
    if itemId in toDeliverFile:
        try:
            os.remove(f"{itemId}.raw.json")
            os.remove(f"{itemId}.json") 
            del toDeliverFile[itemId]
            del toDeliverThread[itemId]     
            return {"status": "success", "message": "Deleted json files"}
        except OSError:
            pass
        try:
            os.remove(f"{itemId}.error.txt")
            del toDeliverFile[itemId]
            del toDeliverThread[itemId]
            return {"status": "success", "message": "Deleted error file"}
        except OSError:
            pass

        return {"status": "failed", "message": "Nothing to delete yet."}
    else:
        raise HTTPException(status_code=400, detail="Item does not exist")

# Get list of current keys / filepaths
@app.get("/list")
async def getList():
    return {"status": "success", "data": toDeliverFile}


