# Read-only smoke test: logs in as admin, opens every main page and reports PHP errors.
# Run: powershell -ExecutionPolicy Bypass -File storage\smoke_test.ps1
$base = 'http://localhost/Ocean_HRMS/public'
$s = New-Object Microsoft.PowerShell.Commands.WebRequestSession
function Token($html) { if ($html -match 'name="_csrf" value="([^"]+)"') { $Matches[1] } }

$login = Invoke-WebRequest "$base/login" -WebSession $s -UseBasicParsing
$r = Invoke-WebRequest "$base/login" -Method Post -WebSession $s -UseBasicParsing -Body @{ _csrf = (Token $login.Content); login = 'admin'; password = 'admin' }
"login -> $($r.BaseResponse.ResponseUri.AbsolutePath)"

$pages = 'dashboard','departments','designations','employees','employees/create','employees/3','employees/3/edit',
  'employees/3?tab=documents','employees/3?tab=leave','employees/3?tab=attendance','employees/3?tab=salary','employees/3?tab=timeline',
  'employees/3/letters/offer','employees/3/letters/appointment','employees/3/letters/experience','employees/3/letters/relieving',
  'attendance','attendance/report','attendance/summary','shifts','employees/logins','notifications','notifications/feed',
  'leave','leave?status=all','leave/types','leave/days?from=2026-10-12&to=2026-10-16','salary','salary/employee/3','queries','queries/create','settings','settings/letterhead',
  'recruitment','recruitment?view=grid','recruitment/interviews','recruitment/qr','recruitment/10','recruitment/10/print','recruitment/1',
  'recruitment/1/offer-letter?ctc=400000&joining_date=2026-10-15','holidays','announcements','apply','change-password'
$bad = 0
foreach ($p in $pages) {
  try {
    $res = Invoke-WebRequest "$base/$p" -WebSession $s -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    $err = $res.Content -match '(Fatal error|Warning</b>|Notice</b>|Deprecated</b>|Uncaught|PDOException|Stack trace)'
    if ($err) { $bad++; "FAIL  $p  (PHP error in output)"; ($res.Content | Select-String -Pattern '(Fatal error|Warning|Uncaught)[^<]{0,200}' -AllMatches).Matches | Select-Object -First 2 | ForEach-Object { "      $($_.Value)" } }
    else { "ok    $($res.StatusCode) $p" }
  } catch {
    $code = $_.Exception.Response.StatusCode.value__
    if ($code -eq 302) { "redir 302 $p -> $($_.Exception.Response.Headers['Location'])" } else { $bad++; "FAIL  $code $p" }
  }
}

try { Invoke-WebRequest "$base/holidays" -Method Post -WebSession $s -UseBasicParsing -MaximumRedirection 0 -Body @{ _csrf = 'wrong' } -ErrorAction Stop | Out-Null } catch { }
$after = Invoke-WebRequest "$base/holidays" -WebSession $s -UseBasicParsing
"invalid CSRF rejected: " + ($after.Content -match 'Invalid token')
"FAILURES: $bad"
