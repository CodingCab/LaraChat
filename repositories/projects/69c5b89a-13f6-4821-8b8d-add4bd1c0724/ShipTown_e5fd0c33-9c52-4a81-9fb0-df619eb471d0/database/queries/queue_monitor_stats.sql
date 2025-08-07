SELECT job_class,
       count(*) as total_count,
       COUNT(CASE WHEN processing_at IS NULL THEN 1 ELSE NULL END) as currently_running,
       AVG(seconds_running) as avg_seconds_running,
       SUM(seconds_running) as sum_seconds_running,
       SUM(seconds_running) / 60 as sum_min_running,
       MAX(seconds_running) as max_seconds_running,
       MIN(seconds_running) as min_seconds_running

FROM modules_queue_monitor_jobs

GROUP BY job_class

ORDER BY sum_seconds_running DESC

LIMIT 500
