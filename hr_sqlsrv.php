{
  "headers": {
    "Content-Type": "application/json",
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Methods": "GET, POST, OPTIONS",
    "Access-Control-Allow-Headers": "Content-Type, Authorization"
  },
  "request_method": {
    "OPTIONS": {
      "response_code": 200,
      "exit": true
    }
  },
  "database": {
    "server": "192.168.50.30",
    "database": "SKTPayroll",
    "username": "sa",
    "password": "ktm@werty123",
    "charset": "UTF-8"
  },
  "input_parameters": {
    "EmpCode": "string",
    "FromDate": "YYYY-MM-DD",
    "ToDate": "YYYY-MM-DD"
  },
  "validations": {
    "check_empty": ["EmpCode", "FromDate", "ToDate"],
    "on_error": {
      "status": "error",
      "message": "EmpCode, FromDate, and ToDate are required."
    }
  },
  "sql_query": {
    "description": "Get total attendance, present days, half days and total present count",
    "query": "SELECT COUNT(att.AttENDanceId) AS Cnt, SUM(CASE WHEN att.Present='Y' THEN 1 ELSE 0 END) as PrCnt, SUM(CASE WHEN att.HalfDayPresent='Y' THEN 0.5 WHEN att.HalfDayPresent='S' THEN 0.5 ELSE 0 END) as halfcnt, SUM(CASE WHEN att.Present = 'Y' THEN 1 ELSE 0 END) + SUM(CASE WHEN att.HalfDayPresent = 'Y' THEN 0.5 WHEN att.HalfDayPresent = 'S' THEN 0.5 ELSE 0 END) AS TotalPrCnt FROM EmployeeAttENDance att INNER JOIN EmployeeMaster emp ON emp.EmployeeId = att.EmployeeId WHERE emp.BiometricCode = ? AND att.AttendanceDate BETWEEN ? AND ?",
    "parameters": ["EmpCode", "FromDate", "ToDate"]
  },
  "responses": {
    "on_success": {
      "status": "success",
      "message": "Employee attendance retrieved successfully!",
      "data": "Array of attendance summary"
    },
    "on_no_data": {
      "status": "error",
      "message": "No records found for the given Employee Code and Date Range."
    },
    "on_connection_error": {
      "status": "error",
      "message": "Connection failed",
      "error_details": "sqlsrv_errors()"
    },
    "on_query_error": {
      "status": "error",
      "message": "Query failed",
      "error_details": "sqlsrv_errors()"
    }
  }
}
