const express = require('express');
const sql = require('mssql');
const cors = require('cors');

const app = express();
const port = 3000;

// Middleware
app.use(cors());
app.use(express.json());

// SQL Server config
const dbConfig = {
    user: 'sa',
    password: 'ktm@werty123',
    server: '192.168.50.30',
    database: 'SKTPayroll',
    options: {
        encrypt: false,
        trustServerCertificate: true,
    }
};

app.get('/attendance', async (req, res) => {
    const { FromDate, ToDate, EmpCode } = req.query;

    if (!FromDate || !ToDate || !EmpCode) {
        return res.status(400).json({
            status: 'error',
            message: 'EmpCode, FromDate, and ToDate are required.'
        });
    }

    try {
        // Connect to DB
        let pool = await sql.connect(dbConfig);

        const result = await pool.request()
            .input('EmpCode', sql.VarChar, EmpCode)
            .input('FromDate', sql.Date, FromDate)
            .input('ToDate', sql.Date, ToDate)
            .query(`
SELECT COUNT(att.AttENDanceId) AS Cnt,
SUM(CASE WHEN att.Present='Y' THEN 1 ELSE 0 END ) as PrCnt,
SUM(CASE WHEN att.HalfDayPresent='Y' THEN 0.5 WHEN att.HalfDayPresent='S' THEN 0.5 ELSE 0 END ) as halfcnt,
SUM(CASE WHEN att.Present = 'Y' THEN 1 ELSE 0 END) +
SUM(CASE WHEN att.HalfDayPresent = 'Y' THEN 0.5 WHEN att.HalfDayPresent = 'S' THEN 0.5 ELSE 0 END) AS TotalPrCnt
FROM EmployeeAttENDance att
INNER JOIN EmployeeMaster emp ON emp.EmployeeId=att.EmployeeId
WHERE emp.BiometricCode = @EmpCode
AND att.AttendanceDate BETWEEN @FromDate AND @ToDate
`);

        const data = result.recordset;

        if (data.length === 0) {
            return res.json({
                status: 'error',
                message: 'No records found for the given Employee Code and Date Range.'
            });
        }

        res.json({
            status: 'success',
            message: 'Employee attendance retrieved successfully!',
            data: data
        });

    } catch (err) {
        console.error(err);
        res.status(500).json({
            status: 'error',
            message: 'Query failed',
            error_details: err
        });
    } finally {
        sql.close();
    }
});

app.listen(port, () => {
    console.log(`Server running on http://localhost:${port}`);
});