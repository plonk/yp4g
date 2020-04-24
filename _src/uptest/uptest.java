import java.io.*;
import java.net.*;
import java.applet.Applet;
import java.awt.*;
import java.awt.event.*;
import java.net.URL;

public class uptest extends Applet implements ActionListener, Runnable{
  
  TextField editStatus;
  Thread thread;
  String strServer, strPostCgi;
  int iPort, iPostSize, iRefreshInterval, iBandWidthLimit;
  
  public void init(){
    strServer = getParameter("Server");
    strPostCgi = getParameter("PostCgi");
    iPort = Integer.parseInt(getParameter("Port"));
    iPostSize = Integer.parseInt(getParameter("PostSize"));
    iRefreshInterval = Integer.parseInt(getParameter("RefreshInterval"));
    iBandWidthLimit = Integer.parseInt(getParameter("BandWidthLimit"));;
    
    editStatus = (TextField)add(new TextField("待機中...", 32));
    
    Button buttonStart = (Button)add(new Button("測定開始"));
    buttonStart.addActionListener(this);
    buttonStart.setActionCommand("Start");
  }
  
  public void actionPerformed(ActionEvent e){
    if(e.getActionCommand() == "Start"){
      if(thread == null){
        thread = new Thread(this);
        thread.start();
      }
    }
  }
  
  public void stop(){
    thread = null;
  }
  
  public void run(){
    String str, strJump = "";
    
    try{
      editStatus.setText("接続中...");
      
      Socket socket = new Socket(strServer, iPort);
      
      PrintWriter out = new PrintWriter(socket.getOutputStream(), true);
      BufferedReader in = new BufferedReader(new InputStreamReader(socket.getInputStream()));
      
      int i;
      long start, current, interval, total = 0;
      String strData  = "rqwerqweoruiysadrqwerqweoruiysadrqwerqweoruiysadrqwerqweoruiysad";
      
      out.println("POST " + strPostCgi + " HTTP/1.1");
      out.println("Host: " + strServer);
      out.println("Content-Length: " + (iPostSize*1024+2));
      out.println("");
      
      editStatus.setText("測定中...");
      
      start = System.currentTimeMillis();
      
      out.print("x=");
      for(i=0; i<1024/strData.length()*iPostSize; i++){
        out.print(strData);
        total += strData.length();
        
        current = System.currentTimeMillis();
        
        if(current-start > iRefreshInterval){
          start = current;
          total = 0;
        }
        
        interval = (long)((double)total*8/iBandWidthLimit)-(current-start);
        if(interval > 0){
          try{
            Thread.sleep(interval);
          }catch(InterruptedException e){}
        }
      }
      out.println("");
      
      for(;;){
        str = in.readLine();
        
        if(str==null || str=="")
          break;
        
        if(str.indexOf("Location") == 0){
          strJump = str;
          strJump = strJump.substring(strJump.lastIndexOf(' ')+1, strJump.length());
        }
      }
      
      out.close();
      in.close();
      socket.close();
      
      getAppletContext().showDocument(new URL(strJump));
      
    }catch(IOException e){
      if(strJump != ""){
        try{
        getAppletContext().showDocument(new URL(strJump));
        }catch(MalformedURLException ue){}
      }else{
        editStatus.setText("エラー");
      }
    }
  }
}
