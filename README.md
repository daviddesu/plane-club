# TODO

[x] Finsh image upload log form and convert to modal
[x] Allow image preview delete and modal clear
[x] Improve upload bar
[x] Finish log list grid and display
[x] Make airports list dynamic
[x] Create individual log modal/page
[x] Edit log
[x] Finish log view
[x] Delete log
[x] Images on s3
[x] Lazy load images as not to spam s3
[x] Image safety checks
[x] Error reporting (Look at sentry)
[x] Analytics (Look at posthog)
[x] Implement the toast lib for all action confirmations
[x] Home page and pricing page
[x] Registration page and implement stripe or other payment provider
[x] Can see other users logs
[x] Change log addition to not add multiple logs per image
[x] Chnage log view to show images view
[x] WHen clikcing on an image, show the log image carrousel starting with the selected image
[x] A page to view logs and a page to view all images - image page will show fullscreen image when clicked
[x] efficiently load images form AWS with pagination for images page and logs page
[x] add video uploads
[x] Handle video compression
[x] Handle HEIC and other non jpeg or PNG upload types
[x] Convert all temp storage to use S3 in production
[x] Change to just uploading one image per log. Submit and add another buttin
[x] Better log saveing process to prevent timeouts and informt he users the log is being created
[x] Make sure temp files are removed once persistsed to s3
[x] Filtered pages to show logs by, airport, aircraft, airline, date etc.
[x] see how we can ustill use local on local dev
[x] Pagination
[x] Add departure/destination airport, and also flight number and landing, takeoff, inflight 
[x] Production queue efficiency 
[x] Email verification on signup
[x] User area upgrades for subscription view and cancel
[x] User delete not working
[ ] Auth themeing
[ ] S3 look at cors to specifiyt he correct domain
[ ] Send to facebook
[ ] Perfection pass
[ ] Beuutify sales page
[ ] Subscription emails and verficiation and trial end prompts and actual
[ ] trial days left banner (colapsable)
[ ] Create prod setup
[ ] Emails on prod
[ ] S3 file backups
[ ] DB backups?
[ ] Setup stripe for local, staging and prod
[ ] register business
[ ] Business bank account
[ ] Update stripe account


## Later

[ ] Allow next log to poputa efrom the last details
[ ] Upload and show avatar to nav bar
[ ] Show avatar on log
[ ] Like other people logs
[ ] Google and facebook auth (clerk maybe?)
[ ] Comment on other peoples logs
[ ] Host videos and safety
[ ] share to social media with a watermark
[ ] Allow logs to be made public
[ ] Show all public logs
[ ] Comment on public logs
[ ] Scale down image safety checks as the user proves trustworthy. e,g, 100% for first month, reduced to a % probbaility that   decreases over time, probably to a minumum of 10% of uploads checked?


## Local env
- PHP
- postgres
- node
- npm
- nginx
- stripe cli (https://www.youtube.com/watch?v=2_BsWO5WRmU)

## Dependencies:

sudo apt update
sudo apt install ffmpeg (compression)

sudo apt install imagemagick (image conversion, specifically heic to jpeg)

## Connect to staging/prod db

Connect to DB = fly postgres connect -a plane-club-db

## Listen for stripe webhook locally with stripe cli

stripe listen --forward-to plane-club.test/stripe/webhook --skip-verify

## Running web server and task server (for queues)
### Summary of Commands

#### Building the Docker Image
```bash
docker build -t plane-club-app .
```

#### Running Locally
Web Server: `docker run -p 8080:8080 plane-club-app`
Queue Worker: `docker run -e APP_PROCESS=queue plane-club-app`
Scheduler: `docker run -e APP_PROCESS=scheduler plane-club-app`

#### Deploying to Fly.io
```bash
fly deploy
```

#### Scaling Processes
```bash
fly scale count web=2
fly scale count queue=1
fly scale count scheduler=1
```

#### Monitoring Logs
```bash
fly logs --process-group web
fly logs --process-group queue
fly logs --process-group scheduler
```

### Monitoring and Managing Jobs

#### Viewing Jobs in the Database
You can monitor queued jobs directly in your database by querying the jobs table.

```sql
SELECT * FROM jobs;
```

#### Clearing the Queue
To delete all pending jobs from the database queue:

```bash
php artisan queue:flush
```

#### Handling Failed Jobs
Laravel stores failed jobs in the failed_jobs table.

View Failed Jobs:

```sql
SELECT * FROM failed_jobs;
```

#### Retry Failed Jobs:

```bash
php artisan queue:retry all
```

#### Delete Failed Jobs:

```bash
php artisan queue:flush --failed
```
