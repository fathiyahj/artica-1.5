unit tcpip;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,unix,BaseUnix,libc,RegExpr in 'RegExpr.pas';

  type
  ttcpip=class


private
   function FILE_TEMP():string;
   procedure DeleteFile(TargetPath:string);
   function     TCP_GetFlags(ifname: String): Integer;


public
    MEMORY_LIST_NIC:TStringlist;
    procedure   Free;
    constructor Create;
    function     LOCAL_IP_FROM_NIC(ifname:string):string;
    function     IP_BROADCAST_INTERFACE(ifname:string):string;
    function     IP_MASK_INTERFACE(ifname:string):string;
    function     IP_ADDRESS_INTERFACE(if_name:ansistring):ansistring;
    function     IP_LOCAL_GATEWAY(ifname:string):string;
    function     LIST_NICS():TStringList;
    function     isNetAvailable:boolean;
    function     isPinged(ipaddr:string):boolean;
    function     SYSTEM_GET_LOCAL_IP(ifname:string):string;
    function     LOCAL_MAC(ifname:string):string;
    function     CDIR(ip:string):string;
    function     InterfacesList(): String;
    function     IsWireless(ifname:string): boolean;
    function     IsIfaceDown(ifname:string): boolean;
    function     InterfacesStringList(): Tstringlist;
    function     PHP_INTERFACES():string;
    procedure    InterfacesStringListMEM();
END;

implementation

constructor ttcpip.Create;
begin
end;
//##############################################################################
procedure ttcpip.free();
begin

end;
//##############################################################################
function ttcpip.IsIfaceDown(ifname:string): boolean;
var
  ifc: ifconf;
  ifr: array[0..1023] of ifreq;
  sock, I: Integer;
  RigaOut: String;
  inetname:string;
begin

  result:=false;
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    ifc.ifc_len:= SizeOf(ifr);
    ifc.ifc_ifcu.ifcu_req:= ifr;
    if ioctl(sock, SIOCGIFCONF, @ifc)= 0 then begin
      for I:= 0 to ifc.ifc_len div SizeOf(ifreq)- 1 do begin
          inetname:=ifr[I].ifr_ifrn.ifrn_name;
          if inetname=ifname then begin
             if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_LOOPBACK)<> 0 then RigaOut:= RigaOut+ ', Loopbak';
             if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_UP)<> 0 then begin
                result:=true;
              end else begin
                  result:=false;
              end;
          end;
      end;
    end;
    libc.__close(sock);
  end;
end;
//##############################################################################
function ttcpip.IsWireless(ifname:string): boolean;
const SIOCGIWNAME= 35585;
var
  ifc: ifconf;
  ifr: array[0..1023] of ifreq;
  sock, I: Integer;
  RigaOut: String;
  inetname:string;
begin

  result:=false;
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    ifc.ifc_len:= SizeOf(ifr);
    ifc.ifc_ifcu.ifcu_req:= ifr;
    if ioctl(sock, SIOCGIFCONF, @ifc)= 0 then begin
      for I:= 0 to ifc.ifc_len div SizeOf(ifreq)- 1 do begin
          inetname:=ifr[I].ifr_ifrn.ifrn_name;
          if inetname=ifname then begin
             if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_LOOPBACK)<> 0 then RigaOut:= RigaOut+ ', Loopbak';
             if ioctl(sock, SIOCGIWNAME, @ifr[I])= 0 then begin
                result:=true;
             end;


           if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_UP)<> 0 then RigaOut:= RigaOut+ ', Up.' else RigaOut:= RigaOut+ ', Down.';
          end;
      end;
    end;
    libc.__close(sock);
  end;
end;
//##############################################################################
function ttcpip.InterfacesList(): String;
const SIOCGIWNAME= 35585;
var
  ifc: ifconf;
  ifr: array[0..1023] of ifreq;
  sock, I: Integer;
  RigaOut: String;
begin
  InterfacesList:= '';
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    ifc.ifc_len:= SizeOf(ifr);
    ifc.ifc_ifcu.ifcu_req:= ifr;
    if ioctl(sock, SIOCGIFCONF, @ifc)= 0 then begin
      for I:= 0 to ifc.ifc_len div SizeOf(ifreq)- 1 do begin
        if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_LOOPBACK)<> 0 then RigaOut:= RigaOut+ ', Loopbak';
        if ioctl(sock, SIOCGIWNAME, @ifr[I])= 0 then RigaOut:= RigaOut+ ', Wireless.';
        if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_UP)<> 0 then RigaOut:= RigaOut+ ', Up.' else RigaOut:= RigaOut+ ', Down.';
        result:= result+ Chr(13)+ Chr(10)+ RigaOut;
      end;
    end;
    libc.__close(sock);
  end;
end;
//##############################################################################
function ttcpip.InterfacesStringList(): Tstringlist;
const SIOCGIWNAME= 35585;
var
  ifc: ifconf;
  ifr: array[0..1023] of ifreq;
  sock, I: Integer;
  RigaOut: String;
  l:Tstringlist;
begin
  l:=Tstringlist.Create();
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    ifc.ifc_len:= SizeOf(ifr);
    ifc.ifc_ifcu.ifcu_req:= ifr;
    if ioctl(sock, SIOCGIFCONF, @ifc)= 0 then begin
      for I:= 0 to ifc.ifc_len div SizeOf(ifreq)- 1 do begin
        if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_LOOPBACK)<> 0 then RigaOut:= RigaOut+ ', Loopbak';
        if ioctl(sock, SIOCGIWNAME, @ifr[I])= 0 then RigaOut:= RigaOut+ ', Wireless.';
        if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_UP)<> 0 then RigaOut:= RigaOut+ ', Up.' else RigaOut:= RigaOut+ ', Down.';
       L.Add(ifr[I].ifr_ifrn.ifrn_name);

      end;
    end;
    libc.__close(sock);
  end;

  L.Add('eth0');
  L.Add('eth1');
  L.Add('eth2');
  L.Add('eth3');
  L.Add('eth4');
  result:=l;
end;
//##############################################################################
procedure ttcpip.InterfacesStringListMEM();
const SIOCGIWNAME= 35585;
var
  ifc: ifconf;
  ifr: array[0..1023] of ifreq;
  sock, I: Integer;
  RigaOut: String;
  l:Tstringlist;
begin
  MEMORY_LIST_NIC:=Tstringlist.Create();
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    ifc.ifc_len:= SizeOf(ifr);
    ifc.ifc_ifcu.ifcu_req:= ifr;
    if ioctl(sock, SIOCGIFCONF, @ifc)= 0 then begin
      for I:= 0 to ifc.ifc_len div SizeOf(ifreq)- 1 do begin
        if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_LOOPBACK)<> 0 then RigaOut:= RigaOut+ ', Loopbak';
        if ioctl(sock, SIOCGIWNAME, @ifr[I])= 0 then RigaOut:= RigaOut+ ', Wireless.';
        if (TCP_GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_UP)<> 0 then RigaOut:= RigaOut+ ', Up.' else RigaOut:= RigaOut+ ', Down.';
        MEMORY_LIST_NIC.Add(ifr[I].ifr_ifrn.ifrn_name);

      end;
    end;
    libc.__close(sock);
  end;

end;
//##############################################################################
function ttcpip.TCP_GetFlags(ifname: String): Integer;
var
  ifr : ifreq;
  sock: Integer;
begin
  TCP_GetFlags:= 0;
  strncpy(ifr.ifr_ifrn.ifrn_name, pChar(ifname), IF_NAMESIZE- 1);
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    if ioctl(sock, SIOCGIFFLAGS, @ifr)>= 0 then begin
      TCP_GetFlags:= ifr.ifr_ifru.ifru_flags;
    end;
    libc.__close(sock);
  end;
end;
//##############################################################################
function ttcpip.LOCAL_IP_FROM_NIC(ifname:string):string;
var
 ifr : ifreq;
 sock : longint;
 p:pChar;


begin
 Result:='';

 strncpy( ifr.ifr_ifrn.ifrn_name, pChar(ifname), IF_NAMESIZE-1 );
 ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
 sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
 if ( sock >= 0 ) then begin
   if ( ioctl( sock, SIOCGIFADDR, @ifr ) >= 0 ) then begin
     p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
     if ( p <> nil ) then Result :=  p;
   end;
   libc.__close(sock);
 end;
end;
//##############################################################################
function ttcpip.IP_ADDRESS_INTERFACE(if_name:ansistring):ansistring;
var
 ifr : ifreq;
 sock : longint;
 p:pChar;

begin
 Result:='0.0.0.0';
 strncpy( ifr.ifr_ifrn.ifrn_name, pChar(if_name), IF_NAMESIZE-1 );
 ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
 sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
 if ( sock >= 0 ) then begin
   if ( ioctl( sock, SIOCGIFADDR, @ifr ) >= 0 ) then begin
     p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
     if ( p <> nil ) then Result :=  p;
   end;
   libc.__close(sock);
 end;
end;
//##############################################################################
function ttcpip.SYSTEM_GET_LOCAL_IP(ifname:string):string;
var
 ifr : ifreq;
 sock : longint;
 p:pChar;


begin
 Result:='';

 strncpy( ifr.ifr_ifrn.ifrn_name, pChar(ifname), IF_NAMESIZE-1 );
 ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
 sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
 if ( sock >= 0 ) then begin
   if ( ioctl( sock, SIOCGIFADDR, @ifr ) >= 0 ) then begin
     p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
     if ( p <> nil ) then Result :=  p;
   end;
   libc.__close(sock);
 end;
end;
//##############################################################################


function ttcpip.IP_MASK_INTERFACE(ifname:string):string;
var
 ifr : ifreq;
 sock : longint;
 p:pChar;


begin
 Result:='';

 strncpy( ifr.ifr_ifrn.ifrn_name, pChar(ifname), IF_NAMESIZE-1 );
 ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
 sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
 if ( sock >= 0 ) then begin
   if ( ioctl( sock, SIOCGIFNETMASK, @ifr ) >= 0 ) then begin
     p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
     if ( p <> nil ) then Result :=  p;
   end;
   libc.__close(sock);
 end;
end;
//##############################################################################
function ttcpip.IP_BROADCAST_INTERFACE(ifname:string):string;
var
 ifr : ifreq;
 sock : longint;
 p:pChar;


begin
 Result:='';

 strncpy( ifr.ifr_ifrn.ifrn_name, pChar(ifname), IF_NAMESIZE-1 );
 ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
 sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
 if ( sock >= 0 ) then begin
   if ( ioctl( sock, SIOCGIFBRDADDR, @ifr ) >= 0 ) then begin
     p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
     if ( p <> nil ) then Result :=  p;
   end;
   libc.__close(sock);
 end;
end;
//##############################################################################
function ttcpip.IP_LOCAL_GATEWAY(ifname:string):string;
var
   tmp:string;
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
   D:boolean;
begin
D:=false;
result:='0.0.0.0';
tmp:='/tmp/sbinroute.tmp';
fpsystem('/sbin/route -n >' + tmp + ' 2>&1');
if not FileExists(tmp) then exit();
RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^0.0.0.0\s+([0-9\.]+).+?'+ifname;
   l:=TstringList.Create;
   l.LoadFromFile(tmp);

for i:=0 to l.Count-1 do begin
           if RegExpr.Exec(l.Strings[i]) then begin
              if d then writeln('/sbin/route -n match ',l.Strings[i]);
              result:=RegExpr.Match[1];
              break;
           end;
end;
if d then writeln('');
l.free;
RegExpr.free;

end;
//##############################################################################
function ttcpip.LIST_NICS():TStringList;
var
   list:TStringList;
   RegExpr,RegExprH,RegExprF,RegExprG,RegExprI:TRegExpr;
   i:integer;
   tmpstr:string;
   ArrayList:Tstringlist;
begin



   tmpstr:=GetTempFileName('',ExtractFileName(ParamStr(0))+'-');
   list:=TStringList.Create;
   ArrayList:=TStringList.Create;
   fpsystem('/sbin/ifconfig -a >'+tmpstr+' 2>&1');
   try
      list.LoadFromFile(tmpstr);
   except

   end;


      FpUnlink(tmpstr);
      RegExpr:=TRegExpr.Create;
      RegExprH:=TRegExpr.Create;
      RegExprG:=TRegExpr.Create;
      RegExprF:=TRegExpr.Create;
      RegExprI:=TRegExpr.Create;
      RegExpr.Expression:='^([a-z0-9\:]+)\s+';
      RegExprF.Expression:='^vmnet([0-9\:]+)';
      RegExprG.Expression:='^sit([0-9\:]+)';
      RegExprH.Expression:='^([a-zA-Z0-9]+):avah';
      RegExprI.Expression:='pan([0-9]+)';

      for i:=0 to list.Count -1 do begin
        if RegExpr.Exec(list.Strings[i]) then begin
           if not RegExprF.Exec(RegExpr.Match[1]) then begin
              if not RegExprH.Exec(RegExpr.Match[1]) then begin
                 if not RegExprI.Exec(RegExpr.Match[1]) then begin
                    if not RegExprG.Exec(RegExpr.Match[1]) then begin
                       if RegExpr.Match[1]<>'lo' then begin
                          ArrayList.Add(RegExpr.Match[1]);
                       end;
                    end;
                 end;
              end;
           end;
        end;
   end;
   result:=ArrayList;
    List.Free;
    RegExpr.free;
    RegExprF.free;
    RegExprH.free;
    RegExprG.free;

end;

//##############################################################################
function ttcpip.isNetAvailable:boolean;
var
   l:Tstringlist;
   i:integer;
   ipaddr:string;
begin

result:=false;
l:=Tstringlist.Create;
l.AddStrings(LIST_NICS());
    for i:=0 to l.Count-1 do begin
      if length(trim(l.Strings[i]))=0 then continue;
      ipaddr:=IP_ADDRESS_INTERFACE(l.Strings[i]);
      if ipaddr<>'0.0.0.0' then begin
         result:=true;
         break;
      end;
    end;

    l.free;

end;
//##############################################################################
function ttcpip.FILE_TEMP():string;
var
   stmp:string;
begin
stmp:=FormatDateTime('hhnnss', Now);
result:=GetTempFileName('',ExtractFileName(ParamStr(0))+'-'+stmp+'-')
end;
//##############################################################################
procedure ttcpip.DeleteFile(TargetPath:string);
Var F : Text;

begin
  if not FileExists(TargetPath) then exit;
  TRY
    Assign (F,TargetPath);
    Erase (f);
  EXCEPT

  end;
end;
//#############################################################################
function ttcpip.isPinged(ipaddr:string):boolean;
var
   tmpstr:string;
   l:Tstringlist;
   RegExpr:TRegExpr;
   success:boolean;
   i:integer;
begin
  tmpstr:=FILE_TEMP();
  result:=true;
  fpsystem('/bin/ping -q -c 1 -s 16 -W1 -Q 0x02 '+ipaddr+' >'+tmpstr+' 2>&1');
  l:=Tstringlist.CReate;
  try
     l.LoadFromFile(tmpstr);
  except
     DeleteFile(tmpstr);
     exit();
  end;
RegExpr:=TRegExpr.Create;
DeleteFile(tmpstr);
RegExpr.Expression:='0 received, 100.+?packet loss, time 0ms';
For i:=0 to l.count-1 do begin
    if RegExpr.Exec(l.strings[i]) then begin
       result:=false;
       l.free;
       RegExpr.free;
       exit;
    end;
end;




end;
//##############################################################################
function ttcpip.LOCAL_MAC(ifname:string):string;
var
  ifr : ifreq;
  sock: Integer;
begin
  result:= '';
  strncpy(ifr.ifr_ifrn.ifrn_name, pChar(ifname), IF_NAMESIZE- 1);
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    if ioctl(sock, SIOCGIFHWADDR, @ifr)>= 0 then begin
      result:= IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[0], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[1], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[2], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[3], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[4], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[5], 2);
    end;
    libc.__close(sock);
  end;
end;
//##############################################################################
function ttcpip.PHP_INTERFACES():string;
var
   l:Tstringlist;
   t:tstringlist;
   interfaces:string;
   i:integer;
begin
   l:=Tstringlist.CReate;
   t:=Tstringlist.CReate;
   t.AddStrings(InterfacesStringList());
   for i:=0 to t.COunt-1 do begin
       interfaces:=LOCAL_IP_FROM_NIC(t.Strings[i]);
       if t.Strings[i]='lo' then continue;
       if trim(interfaces)='0.0.0.0' then continue;
       l.Add('"'+t.Strings[i]+'"=>"'+interfaces+'",');
   end;

   result:=l.Text;
   l.free;
   t.free;

end;
//##############################################################################
function ttcpip.CDIR(ip:string):string;
var
   l:Tstringlist;
   ip_f,ip_t,tmp:string;
   i:integer;
   RegExpr:TRegExpr;
begin
    RegExpr:=TRegExpr.CReate;
    RegExpr.Expression:='([0-9]+)\.([0-9]+)\.([0-9]+)';
    if not RegExpr.Exec(ip) then begin
       RegExpr.free;
       exit;
    end;

    ip_f:=RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'.0';
    ip_t:=RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'.255';
    tmp:='/tmp/ipcalc.tmp';
    fpsystem('/usr/share/artica-postfix/bin/ipcalc -n -b '+ip_f+' '+ip_t + ' >'+tmp+' 2>&1');
    l:=Tstringlist.CReate;
    L.LOadFromFile(tmp);
    RegExpr.Expression:='^Network:\s+(.+)';
    for i:=0 to l.count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
           result:=trim( RegExpr.Match[1]);
           break;
        end;

    end;
   l.free;
   RegExpr.free;

end;
//##############################################################################





end.
