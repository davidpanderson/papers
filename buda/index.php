<div style="max-width: 700px; font-family:Trebuchet MS; line-height:1.4" align=justify>
<center>
<h1>Containerized Volunteer Computing</h1>
<p>
David P. Anderson
<br>
Vitalii Koshura
<br>
Charlie Fenton
<p><p>
June 1, 2025
</center>

<h2>Abstract</h2>
<p>

"Volunteer computing" is the use of consumer digital devices,
such as desktop and laptop computers, tablets, and smartphones,
for high-throughput scientific computing.
The pool of such devices is heterogeneous:
it includes computers with many types and versions
of operating systems (Windows, MacOS, Linux)
as well as varied software and libraries.
It is difficult for scientists to create
versions of their applications that run natively on all these computers.
Container systems such as Docker can help solve this problem:
scientists can package applications and their
software dependencies as Docker "images",
which can run on any computer where Docker is installed.
<p>
We have added support for Docker in BOINC,
a widely-used platform for volunteer computing.
We have also developed web-based interfaces for job submission and control,
and a web portal offering these features to all scientists.
Together, these features simplify access to volunteer computing,
making it usable by more scientists.

<h2>1. Introduction</h2>

<p>
The bulk of the world's computing power is not in data centers,
but in the billions of CPUs and GPUs of
consumer devices such as home and laptop computers.
"Volunteer computing" makes this computing power available,
at little or no cost, to scientists.
People can donate the
power of their computers to research projects of their choosing.
They participate by installing
a program that downloads and executes jobs from
servers operated by science projects.
There are currently about 30 such projects in many scientific areas and at
many institutions.
Their research has
resulted in numerous papers in top scientific journals [ref, ref].

<p>
Volunteer computing works best for large sets of independent jobs
with moderate RAM and storage requirements,
with runtimes ranging from minutes to days,
and preferably with the ability to use GPUs.
A wide range of computational science workloads have these properties.
<p>
Most volunteer computing projects use BOINC,
an open-source middleware system [ref].
BOINC lets scientists create and operate "projects"
(for example, LHC@home from CERN),
and lets volunteers provide resources to these projects.
Volunteers install an application (the BOINC client)
and then choose one or more projects to support.
The client is available for desktop platforms (Windows, MacOS, Linux)
and for mobile devices running Android.
<p>
The use of BOINC by scientists has been limited by several factors.
First, it can be difficult to convert applications to
run on BOINC and on non-Linux platforms.
Second, BOINC's interfaces for submitting and handling jobs
are complex and require programming.
Third, creating and operating a BOINC project requires
a range of skills and resources.
<p>
This paper describes recent changes to BOINC that address
each of these issues.
First, we added support for applications that run in Docker containers.
Second, we created simple web-based interfaces for job submission.
Third, we created BOINC Central, a Web portal that gives scientists
access to volunteer computing power without creating a BOINC project.

<p>
Sections 2 and 3 describe BOINC and Docker respectively.
Section 4 explains how BOINC has been extended to
handle Docker-based applications,
Section 5 describes the job submission interfaces,
Section 6 describes BOINC Central,
and Section 7 offers conclusions.

<h2>2. BOINC architecture</h2>

<p>
BOINC is a client/server system.
Scientists create "projects" by installing the BOINC server
software on a Linux host or cluster.
The server uses a MySQL database to store information
about applications, jobs, and user accounts.
It includes a "scheduler" that handles RPCs
requesting jobs and reporting completed jobs.
It also includes scripts that provide a web site
where volunteers can view their completed jobs,
message one another, and so on.
<p>
Volunteers install the BOINC client program on their computers.
They can then "attach" the client to one or more projects.
The client detects the hardware and software properties
of the computer (CPU and GPU type, driver versions, RAM size, etc.).
It issues RPCs to the schedulers of attached projects.
The request message describes the computer properties, and
the reply can include descriptions of jobs.
The client downloads the job files,
runs the job, uploads the output files,
and reports the completed jobs in subsequent RPCs.
All communication is over HTTP,
so clients can be behind typical firewalls.

<h3>2.1 Models of participation</h3>

<p>
The ways in which volunteers and scientists participate in BOINC
has evolved over time.
In the original model (see Figure 1),
volunteers learn about projects (typically from the BOINC web site)
and attach to them.
Most volunteers stick with the same projects indefinitely.
This creates a problem:
when a scientist starts a new project,
it can be difficult to attract volunteers;
new projects have no guarantee of computing power.

<p>
To address this issue, we added the notion of "account manager",
a web service providing a level of indirection between client and projects.
Volunteers connect the BOINC client to an account manager,
which then dynamically tells the client which projects to run.
We developed an account manager called Science United [ref] that
lets volunteers express preferences for science areas
(such as "Biomedicine" or "Environmental research")
and locations (such as "Europe" or "Harvard").
Each BOINC project has an associated set of keywords.
Science United directs clients to attach to projects
based on keyword preferences.
In this model, new projects can be guaranteed
a certain level of computing power, depending on their keywords.
We call this the "coordinated model" [ref].

<p>
In the coordinated model, scientists are still burdened with
creating and operating projects.
In this paper we present a third model, the "portal model",
that removes this burden.
The portal model involves a project called BOINC Central,
operated by the BOINC project at UC Berkeley,
which provides computing power to scientists
from diverse institutions and science areas.
<p>
<center>
<img src=models.gif width=500>
<br>
Figure 1: BOINC's three models.
The "coordinated model" provides computing power to new projects,
and the "portal model" eliminates the need for scientists to operate projects.
</center>


<h3>2.2 Apps, app versions, and plan classes</h3>
<p>
In BOINC terminology, "app" has a specific meaning.
An "app" is the abstraction of a program;
an "app version" is a specific executable for an app.
Each project maintains a set of apps and app versions.
Each app version is associated with a particular platform
(such as Windows/Intel or MacOS/ARM).
It can also be tagged with a "plan class"
that specifies additional requirements and properties.
For example, the plan class can indicate that the app version is multithreaded,
or uses a particular GPU type, or requires a minimum driver version.

<p>
In the BOINC scheduler, each plan class C has an associated function C(H)
that takes a description of a host H and returns
<ul>
<li> Whether a program of plan class C can run on H
(i.e. whether H has the required hardware and software)
<li> If so, what processing resources will be used
(for example, what fraction of a CPU will be used by a GPU app).
<li> The estimated FLOPS of the program running on H.
This is used to estimate job runtimes on H.
</ul>

<p>
Plan classes are analogous to "instance types" in
cloud systems like Amazon Web Services.
But while clouds have a fixed number of node types,
volunteer computing has an unlimited number;
a plan class selects a subset of them.

<p>
In BOINC terminology, a job is called a "workunit".
A workunit is a set of input files,
together with parameters such as the estimated
FLOP count and RAM usage.
A workunit can have one or more "instances".
If an instance fails, new instances are created up to a limit.
Projects can optionally have each workunit replicated
on two or more hosts, to ensure the correctness of the results.

<p>
Jobs are submitted to apps, not to app versions.
Job instances may be processed using any of the app's versions.
<p>
Different versions of an app may be dispatched to a host:
perhaps one to use its GPU
and another to use the remaining CPUs.

<p>
The files making up app versions are "code-signed".
Each project creates a public/private key pair.
The private key is kept on a offline computer
that is used to create cryptographic signatures of files.
The BOINC client checks these signatures.
This ensures that, even if a project server is hacked,
BOINC cannot be used to distribute malware.
However, it means that creating an app version requires
manual work by project admins.

<center>
<img src=apps.gif width=600>
<br>
Figure 1: A BOINC app can have multiple versions.
</center>

<h3>2.3 Packaging apps</h3>
<p>
The BOINC runs each job in its own "slot directory",
containing the job's input and output files.
The directory is initialized with a "job configuration file"
indicating, for example, which GPU the job should use,
or how many cores a multithreaded app should use.
<p>
The BOINC client communicates with running apps: for example,
the client can tell the app to suspend itself, quit, or checkpoint;
the app can tell the client that it has checkpointed,
or it can report its CPU usage or working-set size.
This message-passing is done via a shared memory segment.

<p>
Prior to the current work, BOINC offered three ways to package apps.
<ol>
<li> Native: app versions are linked with a BOINC runtime library,
which manages communication with the client.
A separate app version must be built separately for each platform.
<li> Wrapper: the app version's main program is a BOINC-supplied
"wrapper" program that communicates with the client
and uses an OS-specific mechanism (e.g. Unix signals)
to control the worker process.
<li> VirtualBox: the app version's main program is a BOINC-supplied
"VirtualBox wrapper" program,
and it includes a VM image containing the worker program.
Such app versions have a plan class specifying that they can
run only on computers on which VirtualBox is installed.
</ol>

<p>
Each of these options has drawbacks.
Options 1) and 2) require building apps on possibly unfamiliar
systems such as Visual Studio (Windows) and XCode (MacOS).
They also don't work for apps that require particular system libraries,
or that require software tools such as Python.
Option 3) doesn't have these issues,
but has several of its own:
a) creating VM images is difficult;
b) launching VMs is slow,
and c) programs running in VirtualBox VMs cannot access GPUs.

<h2>3. Container systems</h2>

<p>
In container systems,
processes on a "host" computer run in a "guest" environment.
The guest environment provides 
operating system interfaces, libraries, and installed software
that can differ from those of the host.
In addition,
the guest environment is "contained": processes running in it
cannot access files on the host system.
Their resource usage and their network access can be limited.
The namespaces of users and groups can differ from those of the host.

<p>
Support for containerization was implemented in Linux,
with the chroot() system call,
which changes the root directory of a process,
and with mechanisms like cgroups and namespaces [ref].

<h3>3.1 Docker and Podman</h3>

<p>
Building on these Linux features, several systems
have been developed for describing and running containers.
The most widely used is Docker [Merkel].
In Docker terminology,
an "image" describes a guest environment;
a "container" is a running instance of an image.

<p>
A Docker image is defined by a "Dockerfile".
This specifies a "base image" (typically corresponding
to a particular Linux distro, perhaps with some software
already installed), followed by instructions for
installing additional software and/or files.

<p>
The Docker client software manages the images and containers on a host.
It provides a command-line interface for "building" images from Dockerfiles,
and for creating and running containers from images.

<p>
Docker is more efficient than VM systems in terms
of storage, startup time, and runtime performance [Felter].
A Docker base image does not include an OS kernel:
that's provided by the host.
It includes only the user-level part of the system,
so image files are typically tens of MB instead of several GB.
Also, when Docker builds an image,
each of the additions is stored as a separate "layer" file.
If another image uses the same addition, it uses the same file.

<p>
Creating and running a Docker container does not involve
booting an OS, as VMs do.
So it's fairly fast - about 1 second.
In contrast, launching a VirtualBox VM can
take up to 60 seconds and does lots of disk I/O.
This difference is critical for BOINC.
Typically a volunteer host with N CPUs has N BOINC jobs.
If these are VirtualBox jobs, and the BOINC client runs them
all at startup, it can cause the host to become unusably slow
for several minutes.

<p>
Unlike VirtualBox VMs, Docker containers are able to
access the host's GPU(s).
This is important for BOINC, since
the majority of floating-point capacity
in home computers is in their GPUs [ref].

<p>
Docker is a commercial product.
There is a free, open-source equivalent called Podman.
The architecture of Podman is different from Docker:
Docker uses a daemon process, while Podman does not.
However, the features and command-line syntax of Podman are nearly identical.

<p>
We chose to use Podman in BOINC.
However, BOINC works with Docker too:
if Docker is installed on a volunteered computer,
BOINC will detect and use it.

<p>
In the remainder of this paper, we will use "Docker"
to refer to either Docker or Podman.

<h3>3.2 Docker on non-Linux systems</h3>

<p>
About 80% of BOINC volunteer computers run Windows;
the rest are about half Linux and half MacOS.
Docker requires Linux.
Fortunately, Docker can be used on both Windows and MacOS,
by running a Linux VM on the system.

<p>
On Windows, such a VM can be provided by "Windows Subsystem for Linux" (WSL),
which is built into recent versions of Windows (10 and 11) [ref].
WSL uses files called "Linux distros",
which contain the user-level part of distros such as Ubuntu and Red Hat.
These can be downloaded from the Windows Store or from other servers.

<p>
On MacOS, Podman uses a Linux VM based on QEMU [ref].
This is downloaded and started as part of the Podman installation process.

<h2>4. Supporting Docker-based apps in BOINC</h3>

<p>
Starting in 2024, we extended BOINC to support Docker-based apps.
This involved changes to many BOINC components.

<p>
<h3>4.1 Installing Podman on volunteer computers</h3>

<p>
Many BOINC volunteers are non-technical.
We have tried to make the installation of
the BOINC client as simple as possible,
so that such users will complete the process.
In the typical case it involves three mouse clicks.
We now want to install Podman as part of this process.

<p>
On Linux this is simple.
The BOINC installer is a package, and we include Podman as a dependency,
so that it is automatically installed along with the BOINC client.

<p>
On Windows, we created a WSL distro:
a minimal Linux (Alpine) image in which Podman is installed.
The BOINC Windows installer enables WSL,
and downloads and installs this WSL distro.

<p>
On MacOS, the BOINC installer downloads and runs a Podman installer.
This requires an additional step: running a podman command
to initialize and start the QEMU virtual machine.
We modified the BOINC client to do this on startup.

<h3>4.2 Selecting and running Docker apps</h3>

<p>
The process by which a BOINC server learns that a client
is Docker-capable, and sends it Docker jobs,
involves several interconnected parts.

<p>
First, the BOINC client detects the presence of Podman or Docker
on startup.
On Linux and MacOS, this is done by running a command
("docker --version" or "podman --version")
and checking its output.
On Windows, the client queries the Windows registry for
the list of installed WSL distros.
For each distro, it runs a shell in the distro and
checks for Podman or Docker as above.
There may be multiple distros in which Podman or Docker is installed.

<p>
Second, scheduler RPC request messages include the Docker information.

<p>
Third, the scheduler has a new plan class "docker".
If an app version has this plan class,
it will be sent only to clients that have Docker or Podman.
Variants of this plan class can be added if (for example)
an app version requires a minimum version of Docker or uses a GPU.

<p>
Finally, the client includes Docker info in the configuration
file passed to jobs (see Section x).
For Docker apps this tells this app
whether to use Docker or Podman,
and (on Windows) which WSL distro to use.

<h3>4.3 Docker wrapper</h3>

<p>
To run a Docker app on a volunteer computer,
we need to run various Docker commands to
create the image, create the container,
start or restart the container, fetch its CPU and memory usage,
check for its completion, and so on.

<p>
We could have added this logic to the BOINC client itself,
but that would require that volunteers install a new client each
time the logic changes.
Instead, we developed a "Docker wrapper" program
that handles these functions.
The Docker wrapper does the following:

<ul>
<li> On startup, read the job configuration file
(Section X) to learn whether to use Docker or Podman,
and (on Windows) what WSL distro to use.

<li> Check whether the image exists; build it if not.

<li> Check whether the container exists; start or restart it accordingly.

<li> Every 1 second, check for and handle quit/ abort message from client.

<li> Every 10 seconds, query the container's
CPU and RAM usage, and report these to the client.

<li> Every 10 seconds, check whether the container has exited.
If so, get its stderr output and exit status.
</ul>
<p>
<center>
<img src=docker_win.gif width=500>
<br>
Figure 1: running a Docker container under BOINC on Windows.
</center>
<p>
Docker is able to mount a directory on the host system
within the container.
The Docker wrapper mounts the job's slot directory
(which contains the job's input files and executables)
in the container,
eliminating the need to copy these files into the container.
The job's output files are written to this directory also.

<h3>4.4 Packaging Docker applications</h3>

<p>
Given the BOINC app version architecture (Sec x)
there are 2 ways to package Docker applications.
First, we introduce some new terminology for Docker apps:

<p>
<ul>
<li> A "science app" is an (abstract) program, say Autodock.
<li> A "science app variant" is an executable file or files
    for specific hardware, say a version of Autodock for CPU,
    or for NVIDIA GPU.
</ul>

<h3>4.4.1 The single-purpose model</h3>

<p>
In this model, BOINC apps have a single purpose:
each science app is a BOINC app,
and each science app variant is a BOINC app version.
There is an app version for each platform.
Each app version contains

<p>
<ul>
<li> the Docker wrapper, compiled for that platform;
<li> the Dockerfile;
<li> the executables (compiled for Linux).
</ul>

<p>
Each workunit contains the input files for that job.

<p>
If there are multiple variants (say, for GPUs)
each one has a corresponding BOINC app version,
with the appropriate plan class, extending the "docker" plan class
(for example, "docker_nvidia_opencl").

<p>
This approach matches the BOINC architecture.
However, it has a limitation:
when a new science app is released,
or a new variant of an existing science app,
new BOINC app versions must be created.
This is a complex operation: for example,
it requires code-signing files on a disconnected computer,
and typically it requires login access to the BOINC server.

<h3>4.4.2 The universal model</h3>

<p>
In this model, there is a single BOINC app
(called BOINC Universal Docker App, or BUDA)
that handles all science applications.
There is a BOINC app version (of the BUDA app) for each platform.
Each app version contains only docker_wrapper,
compiled for that platform.

<p>
Each workunit contains
<ul>
<li> The Dockerfile
<li> the science executables
<li> the job's input files
</ul>
<p>
In this model, new science apps and variants can be deployed
without the need to create BOINC apps or app versions,
or to code-sign files.

<p>
Because they're part of the workunit,
the Dockerfile and science executables are not code-signed.
This is not a significant security vulnerability,
because these files are used only within a container,
and cannot access files on the host outside the job's slot directory.

<h3>4.4.3 BUDA plan classes</h3>

<p>
Recall (Section x) that BOINC uses "plan classes"
to describe the requirements of an app version:
for example, that it needs an NVIDIA GPU and OpenCL drivers
with a minimum version number.
But BUDA has only one app version per platform.
How can we support BUDA apps with GPU or other requirements?

<p>
We do this by extending BOINC to allow workunits to have plan classes.
This required changes to the BOINC client and scheduler.
If the scheduler is deciding whether to send a workunit to a host H,
and the workunit has a plan class C,
the scheduler calls the plan class function C(H)
to checks whether H can support C,
and to get the resource usage and estimated FLOPS.
<p>
When the client receives a workunit with a plan class,
it gets the job's resource usage from the workunit
rather than from the app version.

<h2>5. BUDA web interfaces</h2>

<p>
To make BUDA usable for scientists, we needed to provide two capabilities:

<p>
<ul>
<li> creating science apps and app variants
<li> submitting and managing jobs
</ul>

<p>
We sought to make both processes as simple as possible:
in particular, they should not require any programming,
system administration, or knowledge of BOINC internals.
They should be usable by scientists whose
only computer is a Windows or Mac laptop.

<p>
We implemented these functions via web interfaces
provided by the web site of a BOINC project.

To use these interfaces, a scientist creates an account on the project
(the same type of account used by volunteers).
Project admins can grant permissions to these accounts:
permission to create science apps and app variants,
and/or permission to submit jobs.

<p>
Scientists can also be assigned "resource shares";
BOINC also has a resource allocation system that allocates
computing power fairly among competing users
based on their resource shares [ref].

<h3>5.1 File sandbox</h3>

<p>
A user with either permission has an associated "file sandbox".
The user can add a file to their sandbox by
<ul>
<li> uploading it via the web interface;
<li> copying it from a given URL;
<li> for text files, pasting it into a web form.
This is useful when copying shell scripts from Windows computers;
uploading them directly results in CR/LF line endings,
which cause the script to fail on Linux.
</ul>

<h3>5.2 Creating science apps and variants</h3>

<p>
A user with permission can create a BUDA science app.
This involves giving the app a name and description,
and associating science keywords with it.
See Figure x.
<p>
<center>
<img src=create_app.gif width=600>
<br>
Figure 1: Form for creating a BUDA app.
</center>

<p>
They can then create variants of the science app.
Each variant has the following properties:

<p>
<ul>
<li> The plan class, for GPU variants.
<li> The Dockerfile (from the file sandbox).
<li> Additional files, such as executables and scripts (from the file sandbox).
<li> The names of the input and output files for each job.
<li> Replication parameters: how many instances each job to run,
  and how many failures to tolerate before giving up on the job.
<li> The maximum turnaround time for jobs:
  jobs not returned within this time are treated as failures.
</ul>
<center>
<img src=create_variant.gif width=600>
<br>
Figure 1: Form for creating a BUDA variant.
</center>

<h3>5.2 Submitting and managing jobs</h3>

<p>
Jobs are submitted in "batches".
A batch can consist of a single job or many thousands.

<p>
A batch is described by a "batch zip file".
This is a compressed directory with one subdirectory per job.
Each subdirectory contains

<p>
<ul>
<li> the input files, using the filenames from the app variant;
<li> an optional file containing command-line arguments
  passed to the main program or script.
</ul>

<p>
If an input file is used by all the jobs in the patch,
it can be placed in the top-level directory
rather than duplicated in each subdirectory.

<p>
Batch zip files can be created manually during testing.
For production, they can be created using scripts.

<p>
A batch of jobs can be submitted using a web form
specifying:

<p>
<ul>
<li> A batch zip file (from the file sandbox).
<li> Command-line arguments passed to all jobs in the batch.
<li> The maximum runtime on a typical (currently 4.3 GFLOPS) computer.
    When a job is sent to a volunteer host,
    the runtime limit is adjusted based on the floating-point
    benchmark of that host.
    If the limit is reached, the job is aborted and reported as failed.
</ul>

<p>
After submitting a batch, the scientist can track
its progress on the web site.
They can view lists of in-progress and completed batches
(Fig x).
<center>
<hr>
<img src=buda_batches.gif width=600>
<br>
Figure 1: Web page showing batches.
<hr>
</center>
They can drill down to see the details of an in-progress batch
(Fig y).
<center>
<hr>
<img src=buda_batch.gif width=600>
<br>
Figure 1: Web page showing details of a batch.
<hr>
</center>
They can view completed jobs and their output files.
If there is a problem with a batch,
they can "abort" it, in which case no further jobs
of that batch will be dispatched.

<p>
After a batch has been completed, the scientist
can downloads all the output files in a compressed file.
The scientist can then "retire" the batch;
this deletes all input/output files and database records related to the batch.

<h2>6. BOINC Central</h2>

<p>
Using the features described above,
a BOINC project can allow scientists to easily,
and with no programming or BOINC-specific knowledge,
do high-throughput computing with Docker-based apps,
using volunteered computing resources.

<p>
There remains the issue of creating a BOINC project,
which involves:

<ul>
<li> Getting a public-facing server,
    either on dedicated hardware or on a commercial cloud.
<li> Installing the BOINC server software and its dependencies.
<li> Customizing web pages.
<li> Administering the system on an ongoing basis
(doing updates, doing backups, rotating logs, etc.).
<li> Managing user features like message boards, and dealing with spam.
</ul>

<p>
None of these tasks is extremely difficult,
but together they are more than a typical scientist
is willing or able to do.

<p>
To address this problem, we created and manage a BOINC project called
BOINC Central (ref).
BOINC Central hosts BUDA as well as other widely used (non-Docker)
apps such as Autodock.

<p>
Scientists can apply to BOINC Central for computing power.
If the application is approved,
the scientist's user account is granted permission
to submit jobs.

<p>
BOINC Central can host scientists from many institutions,
doing research in many science areas.
To give volunteers control over who they support,
we use the same system of keywords used by Science United (Section x).
When a scientist registers with BOINC Central,
they are assigned location and science area keywords.

<p>
To implement this,
Scheduler changes to support keywords

<h2>7. Conclusion</h2>

<p>
<h3>7.1 Related work</h3>
<p>
Condor and OSG
<p>
AWS free tier?
Google?
<p>
OpenOnDemand
(ruby on rails)


Most of the above give you a shell on a fast computer;
not HTC
<p>

web assembly efforts (volunteer)

<p>
Volunteer computing has the potential to provide computational scientists
with large amounts of computing power at low cost.
It has been used successfully by a number of research projects:
SETI@home, Einstein@home, Folding@Home, World Community Grid,
LHC@home, etc.
However, there have been difficulties
in adapting applications to volunteer computing frameworks,
and in deploying and maintaining volunteer computing projects.
This difficulties have limited the adoption of volunteer computing.

<p>
The work described in this paper is intended to eliminate these difficulties.
Many scientists already use Docker to package their apps
and run them on clouds and clusters.
They can now run these containers, with no modifications, using BOINC.
BUDA lets scientists manage apps and submit jobs
entirely through simple web interfaces.
BOINC Central eliminates the need for scientists to operate BOINC servers.

<p>
These advances are relatively recent.
We hope that they lead to a wider adoption of volunteer computing
by scientists,
and that in turn leads to an expansion of the volunteer population.

<h2>References</h2>

<ol>
<li> David P. Anderson. Globally Scheduling Volunteer Computing. Future Internet 13(9), 229; https://doi.org/10.3390/fi13090229. August 31, 2021.
<li> David P. Anderson. BOINC: A Platform for Volunteer Computing. Journal of Grid Computing 18(1), p. 99-122. DOI 10.1007/s10723-019-09497-9. 2020.
<li> David P. Anderson and Kevin Reed. Celebrating Diversity in Volunteer Computing. 42nd Hawaii International Conference on System Sciences (HICSS), (Best Paper Award). January 5-8, 2009
<li> D.P. Anderson, C. Christensen, and B. Allen. Designing a Runtime System for Volunteer Computing. Supercomputing '06 (The International Conference for High Performance Computing, Networking, Storage and Analysis), Tampa. November 2006
<li> Calegari, P., Levrier, M. and Balczyński, P., 2019. Web portals for high-performance computing: a survey. ACM Transactions on the Web (TWEB), 13(1), pp.1-36.
<li> Chalker, A., Franz, E., Rodgers, M., Dockendorf, T., Johnson, D., Sajdak, D., White, J.P., Plessinger, B.D., Zia, M., Gallo, S.M. and Settlage, R.E., 2021. Open OnDemand: State of the platform, project, and the future. Concurrency and Computation: Practice and Experience, 33(19), p.e6114.
<li> Felter, W., Ferreira, A., Rajamony, R. and Rubio, J., 2015, March. An updated performance comparison of virtual machines and linux containers. In 2015 IEEE international symposium on performance analysis of systems and software (ISPASS) (pp. 171-172). IEEE.
<li> Hudak, D., Johnson, D., Chalker, A., Nicklas, J., Franz, E., Dockendorf, T. and McMichael, B.L., 2018. Open OnDemand: A web-based client portal for HPC centers. Journal of Open Source Software, 3(25), p.622.
<li> Levshina, T., Sehgal, C. and Slyz, M., 2012, December. Supporting Shared Resource Usage for a Diverse User Community: the OSG Experience and Lessons Learned. In Journal of Physics: Conference Series (Vol. 396, No. 3). IOP Publishing.
<li>Merkel, Dirk. "Docker: lightweight linux containers for consistent development and deployment." Linux j 239.2 (2014): 2.
<li> Publications by BOINC Projects (web page): https://boinc.berkeley.edu/pubs.php
<li> BOINC Central web site: https://boinc.berkeley.edu/central/
<li> Cloud-native, high throughput grid computing using the AWS HTC-Grid solution
by Carlos Manzanedo Rueda, Clement Rey, Kirill Bogdanov, and Richard Nicholson on 28 FEB 2022: https://aws.amazon.com/blogs/hpc/cloud-native-high-throughput-computing-with-aws-htc-grid/
<li> Folding@home publications: https://foldingathome.org/papers-results/
</ol>
</div>
