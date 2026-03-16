# PetPeso🐾

PetPeso is a small web app designed to track pet weight records in a
simple and visual way.

The name **PetPeso** literally means *"pet's weight"*.

The goal of the project is intentionally simple:\
register pet weight entries and visualize their evolution over time.

The app is built with **Vue 3 + Vite** on the frontend and a very small
**PHP + JSON backend** that handles persistence.

It is designed to stay **lightweight, practical, and easy to deploy**,
especially on classic **FTP/shared-hosting environments**.

Instead of using a heavy backend stack, the app relies on:

-   a small PHP API
-   JSON files for storage
-   a compiled frontend build

The core workflow is simple:

-   register new weight entries
-   review past records in a table
-   visualize the evolution in a chart

Sample data is currently written in **Spanish**.

------------------------------------------------------------------------

# Why this project exists

PetPeso was created to solve a very specific everyday task:\
**keeping track of pet weight changes in a clear and structured way.**

The focus of the project is:

-   clarity
-   ease of use
-   clean UI
-   minimal technical overhead

Rather than building a complex system, the goal was to create a **small,
focused, and user-friendly tool** with a clean and modern interface.

------------------------------------------------------------------------

# How it works

The project combines a small frontend application with a minimal PHP
backend.

## Frontend

The user interface lives in:

    src/

It is built with **Vue 3** and bundled with **Vite**.

## Backend

The backend is intentionally simple:

    api/weights.php

This PHP script reads and writes data stored in JSON files.

Data storage:

    data/pets.json
    data/weights-history.json

When deployed together, the frontend and backend work as a simple
**PHP-based web app**.

------------------------------------------------------------------------

# Screenshot

Screenshot stored in:

    public/docs/screenshot_desktop.png

Preview:

![Desktop screenshot](public/docs/screenshot_desktop.png)

------------------------------------------------------------------------

# Local development

To run the project locally:

``` sh
npm install
npm run dev:full
```

This starts:

-   the Vue development server
-   the PHP backend for local testing

------------------------------------------------------------------------

# Build for deployment

To prepare the app for upload:

``` sh
npm run build
```

Then upload the following folders to your server:

    dist/
    api/
    data/

Your hosting environment must allow **PHP to write to**:

    data/weights-history.json

------------------------------------------------------------------------

# Quality checks

Recommended checks before publishing:

``` sh
npm run lint
npm run build
```

------------------------------------------------------------------------

# Deployment notes

If the app works locally but shows an **empty table in production**,
check the following files on the server:

    api/weights.php
    data/pets.json
    data/weights-history.json

When deploying through FTP, it may also help to **delete old assets
before uploading a new build**, as browsers sometimes cache outdated
files.

The deployed structure should contain:

    dist/index.html
    dist/assets/...
    api/weights.php
    data/pets.json
    data/weights-history.json

------------------------------------------------------------------------

# Password security

## UI password popup

To set your own password that the front-end will ask before add/edit/delete actions:

``` sh
fallbackPassword = '12345'
```

Note: this **does not replace real authentication**.  
It only provides lightweight UI protection, useful if you want to share your pet data with friends or family without the risk of accidental changes.

------------------------------------------------------------------------

# Data structure

## data/pets.json

Each pet defines at least:

-   id
-   name
-   birthday
-   primaryColor
-   photo
-   formPhoto
-   backPhoto
-   weightKey

------------------------------------------------------------------------

## data/weights-history.json

Each record stores:

-   id
-   date
-   age
-   one weight field for each configured `weightKey`

------------------------------------------------------------------------

# Current UX features

-   add, edit, and delete actions require a password confirmation
-   successful actions show a snackbar notification
-   each pet avatar remembers the selected side for the current browser
    session
-   weight inputs use custom `- / +` controls with **100g steps**

------------------------------------------------------------------------

---

# Final note🐾

The code is **free to use**. No license is required.

Simply add your pets to `pets.json`, include their avatars and photos, build, upload the project to an FTP server, and you're ready to go.🐾