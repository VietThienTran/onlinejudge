/*
 * Copyright 2008 sempr <iamsempr@gmail.com>
 *
 * Refacted and modified by zhblue<newsclan@gmail.com>
 * Bug report email newsclan@gmail.com
 *
 * This file is part of HUSTOJ.
 *
 * HUSTOJ is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * HUSTOJ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HUSTOJ. if not, see <http://www.gnu.org/licenses/>.
 */
#define _GNU_SOURCE
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <dirent.h>
#include <time.h>
#include <stdarg.h>
#include <ctype.h>
#include <sys/wait.h>
#include <sys/ptrace.h>
#include <sys/types.h>
#include <sys/user.h>
#include <sys/syscall.h>
#include <sys/time.h>
#include <sys/resource.h>
#include <sys/signal.h>
#include <sys/stat.h>
#include <unistd.h>
#include <errno.h>
#include <mysql/mysql.h>
#include <assert.h>
#include "okcalls.h"
#include "common.h"
#include "language.h"

#define STD_MB 1048576
#define STD_T_LIM 2
#define STD_F_LIM (STD_MB<<5)
#define STD_M_LIM (STD_MB<<7)
#define BUFFER_SIZE 5120


/*copy from ZOJ
 http://code.google.com/p/zoj/source/browse/trunk/judge_client/client/tracer.cc?spec=svn367&r=367#39
 */
#ifdef __i386
#define REG_SYSCALL orig_eax
#define REG_RET eax
#define REG_ARG0 ebx
#define REG_ARG1 ecx
#else
#define REG_SYSCALL orig_rax
#define REG_RET rax
#define REG_ARG0 rdi
#define REG_ARG1 rsi
#endif

struct problem_struct {
    int id;
    int isspj;
    int spj_lang;
    int solution_lang;
    int memory_limit;
    int time_limit;
};

static char oj_home[BUFFER_SIZE];

static int sleep_time;
static int java_time_bonus = 5;
static int java_memory_bonus = 512;
static char java_xms[BUFFER_SIZE];
static char java_xmx[BUFFER_SIZE];
static int oi_mode = 0;
static int full_diff = 0;

static int shm_run = 0;

static char record_call = 0;
static int use_ptrace = 1;
static int compile_chroot = 1;

static int is_verify = 0; //是用来验证数据，还是根据标程出数据

static const char * tbname = "polygon_status";
//static int sleep_tmp;

#ifdef _mysql_h
MYSQL *conn;
#endif

//static char buf[BUFFER_SIZE];

long get_file_size(const char * filename)
{
    struct stat f_stat;
    if (stat(filename, &f_stat) == -1) {
        return 0;
    }
    return (long) f_stat.st_size;
}

void write_log(const char *fmt, ...)
{
    va_list ap;
    char buffer[4096];
    sprintf(buffer, "%s/log/client.log", oj_home);
    FILE *fp = fopen(buffer, "ae+");
    if (fp == NULL) {
        fprintf(stderr, "openfile error!\n");
        system("pwd");
    }
    va_start(ap, fmt);
    vsprintf(buffer, fmt, ap);
    fprintf(fp, "%s\n", buffer);
    printf("%s\n", buffer);
    va_end(ap);
    fclose(fp);
}

int execute_cmd(const char * fmt, ...)
{
    char cmd[BUFFER_SIZE];

    int ret = 0;
    va_list ap;

    va_start(ap, fmt);
    vsprintf(cmd, fmt, ap);
    //printf("%s\n",cmd);
    ret = system(cmd);
    va_end(ap);
    return ret;
}

#define CALL_ARRAY_SIZE 512
unsigned int call_id = 0;
unsigned int call_counter[CALL_ARRAY_SIZE];

void init_syscalls_limits(int lang)
{
    int i;
    memset(call_counter, 0, sizeof(call_counter));
    if (DEBUG)
        write_log("init_call_counter:%d", lang);

    for (i = 0; i == 0 || ok_calls[lang].call[i]; i++) {
        call_counter[ok_calls[lang].call[i]] = HOJ_MAX_LIMIT;
    }
}

FILE * read_cmd_output(const char * fmt, ...)
{
    char cmd[BUFFER_SIZE];

    FILE * ret = NULL;
    va_list ap;

    va_start(ap, fmt);
    vsprintf(cmd, fmt, ap);
    va_end(ap);
    if (DEBUG)
        printf("%s\n", cmd);
    ret = popen(cmd, "r");

    return ret;
}

// read the configue file
void init_mysql_conf()
{
    FILE *fp = NULL;
    char buf[BUFFER_SIZE];
    db.port_number = 3306;
    sleep_time = 3;
    strcpy(java_xms, "-Xms32m");
    strcpy(java_xmx, "-Xmx256m");
    sprintf(buf, "%s/config.ini", oj_home);
    fp = fopen("./config.ini", "re");
    if (fp != NULL) {
        while (fgets(buf, BUFFER_SIZE - 1, fp)) {
            read_buf(buf, "OJ_HOST_NAME", db.host_name);
            read_buf(buf, "OJ_USER_NAME", db.user_name);
            read_buf(buf, "OJ_PASSWORD", db.password);
            read_buf(buf, "OJ_DB_NAME", db.db_name);
            read_buf(buf, "OJ_MYSQL_UNIX_PORT", db.mysql_unix_port);
            read_int(buf, "OJ_PORT_NUMBER", &db.port_number);
            read_int(buf, "OJ_JAVA_TIME_BONUS", &java_time_bonus);
            read_int(buf, "OJ_JAVA_MEMORY_BONUS", &java_memory_bonus);
            read_buf(buf, "OJ_JAVA_XMS", java_xms);
            read_buf(buf, "OJ_JAVA_XMX", java_xmx);
            read_int(buf, "OJ_FULL_DIFF", &full_diff);
            read_int(buf, "OJ_SHM_RUN", &shm_run);
            read_int(buf, "OJ_USE_PTRACE", &use_ptrace);
            read_int(buf, "OJ_COMPILE_CHROOT", &compile_chroot);
        }
        fclose(fp);
    }
}

int is_input_file(const char fname[])
{
    int l = strlen(fname);
    if (l <= 3 || strcmp(fname + l - 3, ".in") != 0)
        return 0;
    else
        return l - 3;
}

void find_next_nonspace(int * c1, int * c2, FILE ** f1, FILE ** f2, int * ret)
{
    // Find the next non-space character or \n.
    while ((isspace(*c1)) || (isspace(*c2))) {
        if (*c1 != *c2) {
            if (*c2 == EOF) {
                do {
                    *c1 = fgetc(*f1);
                } while (isspace(*c1));
                continue;
            } else if (*c1 == EOF) {
                do {
                    *c2 = fgetc(*f2);
                } while (isspace(*c2));
                continue;
            } else if (isspace(*c1) && isspace(*c2)) {
                while (*c2 == '\n' && isspace(*c1) && *c1!='\n')
                    *c1 = fgetc(*f1);
                while (*c1 == '\n' && isspace(*c2) && *c2!='\n')
                    *c2 = fgetc(*f2);
            } else {
                *ret = OJ_PE;
            }
        }
        if (isspace(*c1)) {
            *c1 = fgetc(*f1);
        }
        if (isspace(*c2)) {
            *c2 = fgetc(*f2);
        }
    }
}

void make_diff_out(const char * path)
{
    execute_cmd("echo '------Input------<br>'>>diff.out");
    execute_cmd("head -c 500 data.in>>diff.out");
    execute_cmd("echo '<br>------Answer-----<br>'>>diff.out");
    execute_cmd("head -c 500 '%s'>>diff.out", path);
    execute_cmd("echo '<br>------Your output-----<br>'>>diff.out");
    execute_cmd("head -c 500 user.out>>diff.out");
}

void delnextline(char s[])
{
    int L;
    L = strlen(s);
    while (L > 0 && (s[L - 1] == '\n' || s[L - 1] == '\r'))
        s[--L] = 0;
}

int compare(const char *file1, const char *file2)
{
    int ret = OJ_AC;
    int c1, c2;
    FILE *f1 = fopen(file1, "re");
    FILE *f2 = fopen(file2, "re");

    if (!f1 || !f2) {
        ret = OJ_RE;
    } else
        for (;;) {
            // Find the first non-space character at the beginning of line.
            // Blank lines are skipped.
            c1 = fgetc(f1);
            c2 = fgetc(f2);
            find_next_nonspace(&c1, &c2, &f1, &f2, &ret);
            // Compare the current line.
            for (;;) {
                // Read until 2 files return a space or 0 together.
                while ((!isspace(c1) && c1) || (!isspace(c2) && c2)) {
                    if (c1 == EOF && c2 == EOF) {
                        goto end;
                    }
                    if (c1 == EOF || c2 == EOF) {
                        break;
                    }
                    if (c1 != c2) {
                        // Consecutive non-space characters should be
                        // all exactly the same
                        ret = OJ_WA;
                        goto end;
                    }
                    c1 = fgetc(f1);
                    c2 = fgetc(f2);
                }
                find_next_nonspace(&c1, &c2, &f1, &f2, &ret);
                if (c1 == EOF && c2 == EOF) {
                    goto end;
                }
                if (c1 == EOF || c2 == EOF) {
                    ret = OJ_WA;
                    goto end;
                }
                if ((c1 == '\n' || !c1) && (c2 == '\n' || !c2)) {
                    break;
                }
            }
        }
    end: 
    if (ret == OJ_WA || ret == OJ_PE){
        make_diff_out(file1);
    }
    if (f1)
        fclose(f1);
    if (f2)
        fclose(f2);
    return ret;
}

void update_solution(int solution_id, int result, int time, int memory)
{
    if (result == OJ_TL && memory == 0)
        result = OJ_ML;
    char sql[BUFFER_SIZE];

    sprintf(sql,
            "UPDATE %s SET result=%d,time=%d,memory=%d WHERE id=%d",
            tbname, result, time, memory, solution_id);

    if (mysql_real_query(conn, sql, strlen(sql))) {
        printf("sql= %s\n",sql);
        printf("..update failed! %s\n",mysql_error(conn));
    }
}

void update_not_ac_info(int solution_id, char * buf)
{
    char sql[(1 << 16)];
    sprintf(sql,
            "UPDATE polygon_status SET info = '%s' WHERE id=%d",
            buf, solution_id);

    if (mysql_real_query(conn, sql, strlen(sql))) {
        write_log(mysql_error(conn));
    }
}

void addceinfo(int solution_id)
{
    char ceinfo[(1 << 15)], *cend;
    FILE *fp = fopen("ce.txt", "re");
    cend = ceinfo;
    while (fgets(cend, 1024, fp)) {
        cend += strlen(cend);
        if (cend - ceinfo > 30000)
            break;
    }
    cend = 0;
    update_not_ac_info(solution_id, ceinfo);
    fclose(fp);
}

// write runtime error message back to database
void _add_solution_info_mysql(int solution_id, const char * filename)
{
    char reinfo[(1 << 15)], *rend;
    FILE *fp = fopen(filename, "re");
    rend = reinfo;
    while (fgets(rend, 1024, fp)) {
        rend += strlen(rend);
        if (rend - reinfo > 30000)
            break;
    }
    rend = 0;
    update_not_ac_info(solution_id, reinfo);
    fclose(fp);
}

void addreinfo(int solution_id)
{
    _add_solution_info_mysql(solution_id, "error.out");
}

void adddiffinfo(int solution_id)
{
    _add_solution_info_mysql(solution_id, "diff.out");
}

void umount(char *work_dir)
{
    execute_cmd("/bin/umount -f %s/proc 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/dev 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/lib 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/lib64 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/etc/alternatives 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/usr 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/bin 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/proc 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f bin usr lib lib64 etc/alternatives dev 2>/dev/null");
    execute_cmd("/bin/umount -f %s/* 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/log/* 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/log/etc/alternatives 2>/dev/null", work_dir);
}

int compile(int lang, char * work_dir)
{
    pid_t pid = fork();
    if (pid == 0) {
        struct rlimit LIM;
        int cpu = 20;
        if (lang == LANG_JAVA) {
            cpu = 30;
        }
        LIM.rlim_max = cpu;
        LIM.rlim_cur = cpu;
        setrlimit(RLIMIT_CPU, &LIM);
        alarm(cpu);
        LIM.rlim_max = 100 * STD_MB;
        LIM.rlim_cur = 100 * STD_MB;
        setrlimit(RLIMIT_FSIZE, &LIM);

        if (lang != LANG_JAVA) {
            LIM.rlim_max = STD_MB << 11;
            LIM.rlim_cur = STD_MB << 11;
            setrlimit(RLIMIT_AS, &LIM);
        }
        
        freopen("ce.txt", "w", stderr);
        execute_cmd("/bin/chown judge %s ", work_dir);
        execute_cmd("/bin/chmod 700 %s ", work_dir);

        if (compile_chroot && lang != LANG_JAVA && lang != LANG_PYTHON3) {
            execute_cmd("mkdir -p bin usr lib lib64 etc/alternatives proc tmp dev");
            execute_cmd("chown judge *");
            execute_cmd("mount -o bind /bin bin");
            execute_cmd("mount -o remount,ro bin");
            execute_cmd("mount -o bind /usr usr");
            execute_cmd("mount -o remount,ro usr");
            execute_cmd("mount -o bind /lib lib");
            execute_cmd("mount -o remount,ro lib");
#ifndef __i386__
            execute_cmd("mount -o bind /lib64 lib64");
            execute_cmd("mount -o remount,ro lib64");
#endif
            execute_cmd("mount -o bind /etc/alternatives etc/alternatives");
            execute_cmd("mount -o remount,ro etc/alternatives");
            execute_cmd("mount -o bind /proc proc");
            chroot(work_dir);
        }
        while (setgid(1536) != 0)
            sleep(1);
        while (setuid(1536) != 0)
            sleep(1);
        while (setresuid(1536, 1536, 1536) != 0)
            sleep(1);

        execvp(languages[lang].compile_cmd[0],
               (char * const *) languages[lang].compile_cmd);

        if (DEBUG)
            printf("Compile end!\n");
        exit(0);
    } else {
        int status = 0;

        waitpid(pid, &status, 0);
        if (lang == LANG_PYTHON3)
            status = get_file_size("ce.txt");
        if (DEBUG)
            printf("status = %d\n", status);
        execute_cmd("/bin/umount -f bin usr lib lib64 etc/alternatives dev 2>/dev/null");
        execute_cmd("/bin/umount -f %s/* 2>/dev/null", work_dir);
        umount(work_dir);
 
        return status;
    }
}

// 连接 mysql 数据库
int init_mysql_conn()
{
    conn = mysql_init(NULL);
    const char timeout = 30;
    mysql_options(conn, MYSQL_OPT_CONNECT_TIMEOUT, &timeout);

    // set mysql unix socket
    char * mysql_unix_port = db.mysql_unix_port;
    if (strlen(mysql_unix_port) == 0) {
        mysql_unix_port = NULL;
    }
    if (!mysql_real_connect(conn, db.host_name, db.user_name, db.password,
                            db.db_name, db.port_number, mysql_unix_port, 0)) {
        write_log("%s", mysql_error(conn));
        return 0;
    }
    const char * utf8sql = "set names utf8";
    if (mysql_real_query(conn, utf8sql, strlen(utf8sql))) {
        write_log("%s", mysql_error(conn));
        return 0;
    }
    return 1;
}

void _create_solution_file(char *source, int lang)
{
    char src_pth[BUFFER_SIZE];
    // create the src file
    sprintf(src_pth, "Main.%s", languages[lang].file_ext);
    if (DEBUG)
        printf("Main=%s", src_pth);
    FILE *fp_src = fopen(src_pth, "we");
    fprintf(fp_src, "%s", source);
    fclose(fp_src);
}

void get_solution_info(int solution_id, int *p_id, int *lang)
{
    MYSQL_RES *res;
    MYSQL_ROW row;

    char sql[BUFFER_SIZE];
    // get the problem id and user id from Table:polygon_status
    sprintf(sql,
            "SELECT problem_id, language, source FROM polygon_status "
            "WHERE id=%d", solution_id);
    //printf("%s\n",sql);
    mysql_real_query(conn, sql, strlen(sql));
    res = mysql_store_result(conn);
    row = mysql_fetch_row(res);
    *p_id = atoi(row[0]);
    if (row[1] && row[2]) {
        is_verify = 1; //结果中含有源码字段说明该记录是用于验证的
        *lang = atoi(row[1]);
    } else {
        is_verify = 0;
    }
    if (is_verify) {
        _create_solution_file(row[2], *lang);
    }
    if (res != NULL) {
        mysql_free_result(res);  // free the memory
        res = NULL;
    }
}

struct problem_struct get_problem_info(int p_id)
{
    struct problem_struct problem;
    problem.id = p_id;
    // get the problem info from Table:problem
    char sql[BUFFER_SIZE];
    MYSQL_RES *res;
    MYSQL_ROW row;
    sprintf(sql,
            "SELECT spj, spj_source, spj_lang, solution_lang, solution_source, "
            "time_limit, memory_limit FROM polygon_problem WHERE id=%d",
            p_id);
    mysql_real_query(conn, sql, strlen(sql));
    res = mysql_store_result(conn);
    row = mysql_fetch_row(res);
    problem.isspj = atoi(row[0]);
    problem.spj_lang = LANG_CPP; //当前只支持C、C++语言的SPJ
    if (row[3]) {
        problem.solution_lang = atoi(row[3]);
    }
    if (!is_verify) {
        _create_solution_file(row[4], problem.solution_lang);
    }
    problem.time_limit = atoi(row[5]);
    problem.memory_limit = atoi(row[6]);
    if (res != NULL) {
        mysql_free_result(res); // free the memory
        res = NULL;
    }
    return problem;
}

char *escape(char s[], char t[])
{
    int i, j;
    for (i = j = 0; t[i] != '\0'; ++i) {
        if (t[i] == '\'') {
            s[j++] = '\'';
            s[j++] = '\\';
            s[j++] = '\'';
            s[j++] = '\'';
            continue;
        } else {
            s[j++] = t[i];
        }
    }
    s[j] = '\0';
    return s;
}

/**
 * 准备需要测试的数据点
 * 成功返回 0，失败返回1
 */
int prepare_files(char * filename, char * infile, int p_id,
                   char * work_dir, char * outfile, char * userfile,
                   int runner_id)
{
    int res = 0;
    sprintf(infile, "%sdata/%d/%s.in", oj_home, p_id, filename);
    execute_cmd("/bin/cp '%s' %s/data.in", infile, work_dir);

    // 判断是输出文件是 out 还是 ans 为后缀
    sprintf(outfile, "%sdata/%d/%s.out", oj_home, p_id, filename);
    if (access(outfile, R_OK) == -1) {
        sprintf(outfile, "%sdata/%d/%s.ans", oj_home, p_id, filename);
        if (access(outfile, R_OK) == -1) {
            res = 1;
        }
    }
    sprintf(userfile, "%srun/%d/user.out", oj_home, runner_id);
    return is_verify ? res : 0;
}

void run_solution(struct problem_struct problem, int lang, char * work_dir,
                  int usedtime)
{
    nice(19);
    // now the user is "judge"
    chdir(work_dir);
    // open the files
    freopen("data.in", "r", stdin);
    freopen("user.out", "w", stdout);
    freopen("error.out", "a+", stderr);
    // trace me
    if(use_ptrace) {
        ptrace(PTRACE_TRACEME, 0, NULL, NULL);
    }
    // run me
    if (lang != LANG_JAVA && lang != LANG_PYTHON3) {
        chroot(work_dir);
    }

    while (setgid(1536) != 0)
        sleep(1);
    while (setuid(1536) != 0)
        sleep(1);
    while (setresuid(1536, 1536, 1536) != 0)
        sleep(1);

    // child
    // set the limit
    struct rlimit LIM; // time limit, file limit& memory limit
    // time limit
    if (oi_mode)
        LIM.rlim_cur = problem.time_limit + 1;
    else
        LIM.rlim_cur = (problem.time_limit - usedtime / 1000) + 1;
    LIM.rlim_max = LIM.rlim_cur;
    //if(DEBUG) printf("LIM_CPU=%d",(int)(LIM.rlim_cur));
    setrlimit(RLIMIT_CPU, &LIM);
    alarm(0);
    alarm(problem.time_limit * 5);

    // file limit
    LIM.rlim_max = STD_F_LIM + STD_MB;
    LIM.rlim_cur = STD_F_LIM;
    setrlimit(RLIMIT_FSIZE, &LIM);
    // proc limit
    if (lang == LANG_JAVA) {
        LIM.rlim_cur = LIM.rlim_max = 200;
    } else {
        LIM.rlim_cur = LIM.rlim_max = 1;
    }

    setrlimit(RLIMIT_NPROC, &LIM);

    // set the stack
    LIM.rlim_cur = STD_MB << 7;
    LIM.rlim_max = STD_MB << 7;
    setrlimit(RLIMIT_STACK, &LIM);
    // set the memory
    LIM.rlim_cur = STD_MB * problem.memory_limit / 2 * 3;
    LIM.rlim_max = STD_MB * problem.memory_limit * 2;
    if (lang == LANG_C || lang == LANG_CPP || lang == LANG_PASCAL)
        setrlimit(RLIMIT_AS, &LIM);

    // run solution
    execvp(languages[lang].run_cmd[0],
           (char * const *) languages[lang].run_cmd);

    // sleep(1);
    fflush(stderr);
    exit(0);
}

int fix_python_mis_judge(char *work_dir, int * ACflg, int * topmemory,
                         int mem_lmt)
{
    int comp_res = OJ_AC;

    comp_res = execute_cmd("/bin/grep 'MemoryError'  %s/error.out", work_dir);

    if (!comp_res) {
        printf("Python need more Memory!");
        *ACflg = OJ_ML;
        *topmemory = mem_lmt * STD_MB;
    }

    return comp_res;
}

int fix_java_mis_judge(char *work_dir, int * ACflg, int * topmemory,
                       int mem_lmt)
{
    int comp_res = OJ_AC;
    execute_cmd("chmod 700 %s/error.out", work_dir);
    if (DEBUG)
        execute_cmd("cat %s/error.out", work_dir);
    comp_res = execute_cmd("/bin/grep 'Exception'  %s/error.out", work_dir);
    if (!comp_res) {
        printf("Exception reported\n");
        *ACflg = OJ_RE;
    }
    execute_cmd("cat %s/error.out", work_dir);

    comp_res = execute_cmd(
            "/bin/grep 'java.lang.OutOfMemoryError'  %s/error.out", work_dir);

    if (!comp_res) {
        printf("JVM need more Memory!");
        *ACflg = OJ_ML;
        *topmemory = mem_lmt * STD_MB;
    }

    if (!comp_res) {
        printf("JVM need more Memory or Threads!");
        *ACflg = OJ_ML;
        *topmemory = mem_lmt * STD_MB;
    }
    comp_res = execute_cmd("/bin/grep 'Could not create'  %s/error.out",
            work_dir);
    if (!comp_res) {
        printf("jvm need more resource,tweak -Xmx(OJ_JAVA_BONUS) Settings");
        *ACflg = OJ_RE;
        //topmemory=0;
    }
    return comp_res;
}

int special_judge(char* oj_home, int problem_id, char *infile, char *outfile,
                  char *userfile)
{
    pid_t pid = fork();
    int ret = 0;
    if (pid == 0) {
        while (setgid(1536) != 0)
            sleep(1);
        while (setuid(1536) != 0)
            sleep(1);
        while (setresuid(1536, 1536, 1536) != 0)
            sleep(1);

        struct rlimit LIM; // time limit, file limit& memory limit

        LIM.rlim_cur = 5;
        LIM.rlim_max = LIM.rlim_cur;
        setrlimit(RLIMIT_CPU, &LIM);
        alarm(0);
        alarm(10);

        // file limit
        LIM.rlim_max = STD_F_LIM + STD_MB;
        LIM.rlim_cur = STD_F_LIM;
        setrlimit(RLIMIT_FSIZE, &LIM);

        ret = execute_cmd("%sdata/%d/spj '%s' '%s' %s", oj_home, problem_id,
                infile, userfile, outfile);
        if (ret)
            exit(1);
        else
            exit(0);
    } else {
        int status;

        waitpid(pid, &status, 0);
        ret = WEXITSTATUS(status);
    }
    return ret;
}

void judge_solution(struct problem_struct problem, int * ACflg, int usedtime, 
                    char * infile, char * outfile, char * userfile,
                    int * PEflg, int lang, char * work_dir, int * topmemory,
                    int solution_id)
{
    int mem_lmt = problem.memory_limit;

    int comp_res;
    if (*ACflg == OJ_AC && usedtime > problem.time_limit * 1000)
        *ACflg = OJ_TL;
    if (*topmemory > mem_lmt * STD_MB)
        *ACflg = OJ_ML;
    // compare
    if (*ACflg == OJ_AC) {
        if (problem.isspj) {
            comp_res = OJ_SE; //因暂无限制SPJ运行环境，Polygon暂不支持SPJ验题
            // comp_res = special_judge(oj_home, problem.id, infile, outfile,
            //                          userfile);
            // if (comp_res == 0) {
            //     comp_res = OJ_AC;
            // } else {
            //     if (DEBUG)
            //         printf("fail test %s\n", infile);
            //     comp_res = OJ_WA;
            //     make_diff_out(outfile);
            // }
        } else {
            comp_res = compare(outfile, userfile);
        }
        if (comp_res == OJ_WA) {
            *ACflg = OJ_WA;
            if (DEBUG)
                printf("fail test %s\n", infile);
        } else if (comp_res == OJ_PE)
            *PEflg = OJ_PE;
        *ACflg = comp_res;
    }
    //jvm popup messages, if don't consider them will get miss-WrongAnswer
    if (lang == LANG_JAVA) {
        comp_res = fix_java_mis_judge(work_dir, ACflg, topmemory, mem_lmt);
    }
    if (lang == LANG_PYTHON3) {
        comp_res = fix_python_mis_judge(work_dir, ACflg, topmemory, mem_lmt);
    }
}

void print_runtimeerror(char * err)
{
    FILE *ferr = fopen("error.out", "a+");
    fprintf(ferr, "Runtime Error:%s\n", err);
    fclose(ferr);
}

void watch_solution(struct problem_struct problem, pid_t pidApp, char * infile,
                    int * ACflg, char * userfile, char * outfile,
                    int solution_id, int lang, int * topmemory, int * usedtime,
                    int PEflg, char * work_dir)
{
    int mem_lmt = problem.memory_limit;
    int isspj = problem.isspj;

    if (DEBUG)
        printf("pid=%d [Solution ID: %d] judging %s\n",
                pidApp, solution_id, infile);

    int status, sig, exitcode;
    struct user_regs_struct reg;
    struct rusage ruse;
    bool first_run = true;
    for (;;) {
        // check the usage
        wait4(pidApp, &status, __WALL, &ruse);
        if(first_run){ // 
            ptrace(PTRACE_SETOPTIONS, pidApp, NULL, PTRACE_O_TRACESYSGOOD 
                   | PTRACE_O_TRACEEXIT 
                //    |PTRACE_O_EXITKILL 
                //  |PTRACE_O_TRACECLONE 
                //  |PTRACE_O_TRACEFORK 
                //  |PTRACE_O_TRACEVFORK
            );
        }
        if (*topmemory < getpagesize() * ruse.ru_minflt)
            *topmemory = getpagesize() * ruse.ru_minflt;

        if (*topmemory > mem_lmt * STD_MB) {
            if (DEBUG)
                printf("out of memory %d\n", *topmemory);
            if (*ACflg == OJ_AC)
                *ACflg = OJ_ML;
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }
        //sig = status >> 8;/*status >> 8 EXITCODE*/
        if (WIFEXITED(status))
            break;
        if ((lang == LANG_C || lang == LANG_CPP) && get_file_size("error.out")) {
            *ACflg = OJ_RE;
            addreinfo(solution_id);
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }

        if (is_verify && !isspj && get_file_size(userfile) > get_file_size(outfile) * 2 + 1024) {
            *ACflg = OJ_OL;
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }

        exitcode = WEXITSTATUS(status);
        /*exitcode == 5 waiting for next CPU allocation
         * ruby using system to run,exit 17 ok
         * Runtime Error:Unknown signal xxx need be added here  
         */
        if (((lang == LANG_JAVA || lang == LANG_PYTHON3) && exitcode == 17) ||
            exitcode == 0x05 || exitcode == 0 || exitcode == 133) {
            //go on and on
            ;
        } else {
            if (DEBUG) {
                printf("status>>8=%d\n", exitcode);
            }
            //psignal(exitcode, NULL);

            if (*ACflg == OJ_AC) {
                switch (exitcode) {
                case SIGCHLD:
                case SIGALRM:
                    alarm(0);
                case SIGKILL:
                case SIGXCPU:
                    *ACflg = OJ_TL;
                    break;
                case SIGXFSZ:
                    *ACflg = OJ_OL;
                    break;
                default:
                    *ACflg = OJ_RE;
                }
                print_runtimeerror(strsignal(exitcode));
            }
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }
        if (WIFSIGNALED(status)) {
            /* WIFSIGNALED: if the process is terminated by signal
             *
             * psignal(int sig, char *s)，like perror(char *s)，print out s,
             * with error msg from system of sig  
             * sig = 5 means Trace/breakpoint trap
             * sig = 11 means Segmentation fault
             * sig = 25 means File size limit exceeded
             */
            sig = WTERMSIG(status);

            if (DEBUG) {
                printf("WTERMSIG=%d\n", sig);
                psignal(sig, NULL);
            }
            if (*ACflg == OJ_AC) {
                switch (sig) {
                case SIGCHLD:
                case SIGALRM:
                    alarm(0);
                case SIGKILL:
                case SIGXCPU:
                    *ACflg = OJ_TL;
                    break;
                case SIGXFSZ:
                    *ACflg = OJ_OL;
                    break;

                default:
                    *ACflg = OJ_RE;
                }
                print_runtimeerror(strsignal(sig));
            }
            break;
        }
        // comment from http://www.felix021.com/blog/read.php?1662

        // WIFSTOPPED: return true if the process is paused or stopped while
        // ptrace is watching on it
        // WSTOPSIG: get the signal if it was stopped by signal

        // check the system calls
        ptrace(PTRACE_GETREGS, pidApp, NULL, &reg);
        call_id = (unsigned int)reg.REG_SYSCALL % CALL_ARRAY_SIZE;
        if (call_counter[call_id]) {
            //call_counter[reg.REG_SYSCALL]--;
        } else if (record_call) {
            call_counter[call_id] = 1;
        } else { //do not limit JVM syscall for using different JVM
            *ACflg = OJ_RE;
            char error[BUFFER_SIZE];
            sprintf(error, "[ERROR] A not allowed system call.\nCall ID:%u",
                call_id);
            write_log(error);
            print_runtimeerror(error);
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
        }
        ptrace(PTRACE_SYSCALL, pidApp, NULL, NULL);
        first_run = false;
    }
    *usedtime += (ruse.ru_utime.tv_sec * 1000 + ruse.ru_utime.tv_usec / 1000);
    *usedtime += (ruse.ru_stime.tv_sec * 1000 + ruse.ru_stime.tv_usec / 1000);
}

void clean_workdir(char * work_dir)
{
    umount(work_dir);
    if (DEBUG) {
        execute_cmd("/bin/rm -rf %s/log/* 2>/dev/null", work_dir);
        execute_cmd("mkdir %s/log/ 2>/dev/null", work_dir);
        execute_cmd("/bin/mv %s/* %s/log/ 2>/dev/null", work_dir, work_dir);
    } else {
        execute_cmd("mkdir %s/log/ 2>/dev/null", work_dir);
        execute_cmd("/bin/mv %s/* %s/log/ 2>/dev/null", work_dir, work_dir);
        execute_cmd("/bin/rm -rf %s/log/* 2>/dev/null", work_dir);
    }
}

void init_parameters(int argc, char ** argv, int * solution_id, int * runner_id)
{
    if (argc < 3) {
        fprintf(stderr, "Usage:%s solution_id runner_id.\n", argv[0]);
        fprintf(stderr, "Multi:%s solution_id runner_id judge_base_path.\n", argv[0]);
        fprintf(stderr, "Debug:%s solution_id runner_id judge_base_path debug.\n", argv[0]);
        exit(1);
    }
    DEBUG = argc > 4;

    int cnt = readlink("/proc/self/exe", oj_home, BUFFER_SIZE);
    if (cnt < 0 || cnt >= BUFFER_SIZE) {
        printf("Get work dir error\n");
        exit(1);
    }
    while (oj_home[cnt] != '/' && cnt > 0) {
        cnt--;
    }
    oj_home[++cnt] = '\0';
    chdir(oj_home); // change the dir

    chdir(oj_home); // change the dir

    *solution_id = atoi(argv[1]);
    *runner_id = atoi(argv[2]);
}

void mk_shm_workdir(char * work_dir)
{
    char shm_path[BUFFER_SIZE];
    sprintf(shm_path, "/dev/shm/onlinejudge%s", work_dir);
    execute_cmd("/bin/mkdir -p %s", shm_path);
    execute_cmd("/bin/ln -s %s %s", shm_path, oj_home);
    execute_cmd("/bin/chown judge %s ", shm_path);
    execute_cmd("chmod 755 %s ", shm_path);
    //sim need a soft link in shm_dir to work correctly
    sprintf(shm_path, "/dev/shm/onlinejudge%s", oj_home);
    execute_cmd("/bin/ln -s %sdata %s", oj_home, shm_path);
}

int count_in_files(char * dirpath)
{
    const char * cmd = "ls -l %s/*.in|wc -l";
    int ret = 0;
    FILE * fjobs = read_cmd_output(cmd, dirpath);
    fscanf(fjobs, "%d", &ret);
    pclose(fjobs);
    return ret;
}

void copy_data_file(char *work_dir,char *data_path, char * filename)
{
    execute_cmd("/bin/cp %s/user.out %s/%s.out", work_dir, data_path,
                filename);
    execute_cmd("chmod 766 %s/*", data_path);
    execute_cmd("chown judge %s/*", data_path);
}

int main(int argc, char** argv)
{
    char work_dir[BUFFER_SIZE];
    int solution_id = 1000;
    int runner_id = 0;
    int problem_id, lang, max_case_time = 0;
    struct problem_struct problem;
    init_parameters(argc, argv, &solution_id, &runner_id);

    init_mysql_conf();

    if (!init_mysql_conn()) {
        exit(0); //exit if mysql is down
    }
    //set work directory to start running & judging
    sprintf(work_dir, "%srun/%d", oj_home, runner_id);
    if (opendir(work_dir) == NULL) {
        execute_cmd("/bin/mkdir -p %s", work_dir);
        execute_cmd("/bin/chown judge %s ", work_dir);
        execute_cmd("chmod 777 %s ", work_dir);
    }
    clean_workdir(work_dir);
    if (shm_run)
        mk_shm_workdir(work_dir);
    chdir(work_dir);
    get_solution_info(solution_id, &problem_id, &lang);

    //get the problem info
    problem = get_problem_info(problem_id);

    if (!is_verify) {
        lang = problem.solution_lang;
    }

    //java is lucky
    // Clang Clang++ not VM or Script
    if (lang >= 2) {
        // the limit for java
        problem.time_limit = problem.time_limit + java_time_bonus;
        problem.memory_limit = problem.memory_limit + java_memory_bonus;
        // copy java.policy
        if (lang == LANG_JAVA) {
            execute_cmd("/bin/cp %s/etc/java0.policy %s/java.policy", oj_home,
                work_dir);
            execute_cmd("chmod 755 %s/java.policy", work_dir);
            execute_cmd("chown judge %s/java.policy", work_dir);
        }
    }

    // compile
    // set the result to compiling
    if (compile(lang, work_dir) != 0) {
        addceinfo(solution_id);
        update_solution(solution_id, OJ_CE, 0, 0);
        mysql_close(conn);
        clean_workdir(work_dir);
        write_log("[Solution ID: %d] Compile Error", solution_id);
        exit(0);
    } else {
        update_solution(solution_id, OJ_RI, 0, 0);
        umount(work_dir);
    }

    char fullpath[BUFFER_SIZE];
    char infile[BUFFER_SIZE];
    char outfile[BUFFER_SIZE];
    char userfile[BUFFER_SIZE];
    char filename[BUFFER_SIZE];

    // the fullpath of data dir
    sprintf(fullpath, "%sdata/%d", oj_home, problem_id);

    // open DIRs
    DIR *dp;
    struct dirent *dirp;
    if ((dp = opendir(fullpath)) == NULL) {
        write_log("No such test data dir:%s!\n", fullpath);
        mysql_close(conn);
        exit(-1);
    } else if (!is_verify) {
        execute_cmd("/bin/rm -rf %s/*.out", fullpath);
    }

    int test_count = count_in_files(fullpath);
    if (test_count == 0) {
        write_log("No input files!\n");
        mysql_close(conn);
        exit(-1);
    }

    int run_result, is_pe;
    run_result = is_pe = OJ_AC;
    int namelen;
    int usedtime = 0, topmemory = 0;

    // read files and run
    while ((run_result == OJ_AC || run_result == OJ_PE) &&
           (dirp = readdir(dp)) != NULL) {
        namelen = is_input_file(dirp->d_name);
        if (namelen == 0)
            continue;

        strncpy(filename, dirp->d_name, namelen);
        filename[namelen] = 0;
        int tmp = prepare_files(filename, infile, problem_id, work_dir,
                      outfile, userfile, runner_id);
        if (tmp) {
            run_result = OJ_NT;
            break;
        }
        init_syscalls_limits(lang);

        pid_t pid = fork();

        if (pid == 0) {
            run_solution(problem, lang, work_dir, usedtime);
        } else {
            watch_solution(problem, pid, infile, &run_result, userfile, outfile,
                    solution_id, lang, &topmemory, &usedtime, is_pe, work_dir);
            if (is_verify) {
                judge_solution(problem, &run_result, usedtime, infile,
                               outfile, userfile, &is_pe, lang, work_dir, &topmemory,
                               solution_id);
            } else {
                copy_data_file(work_dir, fullpath, filename);
            }

            max_case_time =
                        usedtime > max_case_time ? usedtime : max_case_time;
            usedtime = 0;
        }
    }
    if (run_result == OJ_AC && is_pe == OJ_PE)
        run_result = OJ_PE;

    if (run_result == OJ_RE) {
        addreinfo(solution_id);
    }
    usedtime = max_case_time;
    
    if (run_result == OJ_TL) {
        usedtime = problem.time_limit * 1000;
    }

    if (run_result == OJ_WA) {
        adddiffinfo(solution_id);
    }

    update_solution(solution_id, run_result, usedtime, topmemory >> 10);

    clean_workdir(work_dir);
    mysql_close(conn);
    closedir(dp);

    write_log("[Solution ID: %d] Result = %d", solution_id, run_result);
    return 0;
}
