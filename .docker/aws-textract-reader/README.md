### this credential must have read access to the document stored on s3 bucket
### Also, the credential must have access to processing textract
    Managed policy
    AmazonS3ReadOnlyAccess
    AmazonTextractFullAccess

# Run the script:    
    cp credentials to ~/.aws/credentials
or mount
    .aws/credentials to /root/.aws/credentials

#### Change the bucket name for reading as well
    Document
    s3BucketName = "textract-console-eu-west-3-c6b602ac-4f68-496a-b0de-f52ce5a657ce"


### RUN   
    pip install boto3  
    pip install xlwt


# for the server:
## install
    pip install fastapi
    pip install pydantic
    pip install shortuuid
    pip install uvicorn

## to run
Run this command to run the script. The "--app-dir ./app/src" is only necessary when running from the main folder. 
For development use flag --reload
Server runs on port 8000

### Run in the outside folder
    uvicorn server:app --app-dir ./app/src
    
### Run in the src folder
    uvicorn server:app



### post /recognize
    - only takes file stored path like /home/f.pdf or file.pdf as filePath param
    - it will read it from the bucket in the credential

get /status/{id}
delete /acknowledged/{id}
get /list
