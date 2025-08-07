SELECT content->>'$.connection' as connection, content->>'$.name' as name, count(*) as count

FROM `telescope_entries`

WHERE type = 'job'

GROUP BY connection, name

ORDER BY count DESC

LIMIT 150
