# set builder image
FROM node:16-alpine AS builder

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
FROM nginx:1.21-alpine

COPY --from=builder /build/dist/ /data/www/

# expose container port
EXPOSE 80

# command to run on container start
CMD ["nginx", "-g", "daemon off;"]
