SELECT modules_queue_monitor_jobs.job_class,

       SUM(! ISNULL(dispatched_at)) as dispatched_count,
       SUM(! ISNULL(processed_at)) as processed_count,
       SUM(! ISNULL(processing_at)) as processing_count,
       SUM(IFNULL(processed_at, now()) - IFNULL(processing_at, now())) as seconds_running

FROM `modules_queue_monitor_jobs`

GROUP BY job_class;


SELECT
    modules_queue_monitor_jobs.*,
    IFNULL(processed_at, now()) - IFNULL(processing_at, now()) as seconds_running

FROM `modules_queue_monitor_jobs`

WHERE
    processed_at IS NULL
  AND processing_at IS NOT NULL;
