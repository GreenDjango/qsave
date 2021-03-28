# Proxy

# set builder image
FROM node:14.15-alpine AS builder

# set the working directory in the container
WORKDIR /build

# copy the dependencies file to the working directory
COPY package*.json ./

# install dependencies
RUN npm install --silent --no-fund

# copy the src
COPY ./ ./

# compiles and minifies for production
RUN npm run build

# set base image (host OS)
FROM nginx:1.19-alpine

# copy the dependencies file to the working directory
#COPY ./nginx.conf /etc/nginx/conf.d/default.conf
#COPY ./nginx.conf /etc/nginx/conf.d/nginx.conf
#COPY ./nginx.conf /etc/nginx/nginx.conf

COPY --from=builder /build/dist/ /data/www/

# expose container port
EXPOSE 80

# command to run on container start
CMD ["nginx", "-g", "daemon off;"]
