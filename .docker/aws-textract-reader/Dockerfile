FROM ubuntu:22.04


RUN apt-get update

RUN apt-get install -y \
    python3

RUN apt-get install -y \
    pip cron curl tcpdump htop

RUN pip install boto3
RUN pip install xlwt

RUN pip install fastapi
RUN pip install pydantic
RUN pip install shortuuid
RUN pip install uvicorn



# Copy necessary source files
COPY . /var/www/html

WORKDIR /var/www/html

EXPOSE 8000

# Start
CMD [ "bash", "./start_up.sh" ]
