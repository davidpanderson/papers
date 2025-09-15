<?php

$score = '`Score`';
$note = '`Note`';
$measure = '`Measure`';
$nuance_desc = '`NuanceDescription`';
$pft_primitive = '`PFTPrimitive`';
$pause = '<code>Pause</code>';

// https://mathscribe.com/author/jqmath.html

echo '
<html lang="en" xmlns:m="https://www.w3.org/1998/Math/MathML">

<head>
    <meta charset="utf-8">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=UnifrakturMaguntia">
    <link rel="stylesheet" href="https://fred-wang.github.io/MathFonts/LatinModern/mathfonts.css">
    <link rel="stylesheet" href="https://fred-wang.github.io/MathFonts/STIX/mathfonts.css">
    <link rel="stylesheet" href="../mathscribe/jqmath-0.4.3.css">

    <script src="../mathscribe/jquery-1.4.3.min.js"></script>
    <script src="../mathscribe/jqmath-etc-0.4.6.min.js" charset="utf-8"></script>
    <script>M.MathPlayer = false; M.trustHtml = true;</script>
	<style>
		#mathSrc1, #mathSrc2	{ font-size: larger; vertical-align: text-bottom }
		
		table.prec-form-char	{ text-align: center }
		table.prec-form-char td:first-child	{ text-align: right }
	</style>
</head>
<body>
';
$text = "
<div style=\"width: 700; font-size:14px; font-family:Trebuchet MS; line-height:1.4\" align=justify>
<center>
<h1>Modeling performance nuance</h1>

<p>
David P. Anderson
<br>
University of California, Berkeley
<p>
June 1, 2025
</center>

<h2>Abstract</h2>

General Nuance Model (GNM) is
a framework for describing performance nuance
(timing, dynamics, articulation, and pedaling)
in notated keyboard music.
GNM can concisely express nuance closely approximating
that of human performances.
It is designed to allow musicians &mdash; composers and performers &mdash;
to hand-craft expressive renditions of musical works.
This capability has applications in composition, virtual performance,
and performance pedagogy.
We describe these applications,
and the challenges in creating and editing
long and complex nuance specifications.
We also discuss the possibility of inferring nuance descriptions
from recorded human performances.

<p>

<h2>1. Introduction</h2>
<p>
This paper is concerned with 'performance nuance' in notated music.
By this we mean the differences between the score for a piece
and a performance or rendition of it.
We focus on keyboard instruments such as piano.
In this context, nuance has several components:
<p>
<ul>
<li> Timing: tempo, tempo variation, rubato, pauses, rolled chords
and other time-shifting of notes.
<li> Dynamics: crescendos and diminuendos, accents, chord voicing, etc.
<li> Articulation: legato, staccato, portamento, etc.
<li> The use of pedals (sustain, soft, sostenuto),
including fractional pedaling.
</ul>
<p>
For other instruments and voice,
notes may have additional properties such as attack and timbre,
and these properties may change during the note.
The ideas presented here do not encompass these additional factors,
but could possibly be extended to do so.
<p>
Nuance has a central role in western classical music,
as evidenced by the fact that works in the standard repertoire
are performed and recorded thousands of times,
and nuance is the primary difference among these renditions.
<p>
Some scores have nuance indications:
tempo markings, slurs, crescendo marks, fermatas, pedal markings, etc.
These indications do not completely describe the nuance in a human rendition,
because
a) they're imprecise:
e.g. a fermata mark doesn't specify the durations of the sound
or of the following silence;
b) they're ambiguous:
the meaning of marks such as slurs, wedges, and staccato dots
has changed over time, and varies between composers
[Bilson];
c) they're incomplete:
they describe the broad strokes of the composer's intended nuance,
but not the details.
Indeed, western common music notation cannot express
basic aspects of nuance, such the relative volume of notes in a chord.
<p>
In a typical human performance, nuance is guided by score indications
but also by other factors:
the expressive intent of the performer;
stylistic conventions (as understood by the performer);
and the performer's technique and physical limitations,
which can convey difficulty and thus have an expressive role.

<p>
The work described in this paper began with the goal
of developing a software framework for describing performance nuance.
We called the result General Nuance Model (GNM).
GNM has precisely-defined semantics,
and can describe typical human nuance in a compact way;
for example, gestures like crescendos are described in a single primitive
rather than by per-note deviations.
GNM has several key features.
<ul>
<li>
It can express nuance gestures that are continuous (crescendos and accelerandos)
and/or discrete (accents and pauses).
<li>
It provides a powerful way of selecting subsets of notes,
based either on explicit 'tags',
or on note attributes such as chord or metric position.
A 'note selector' is a boolean-valued function of these tags and attributes.
<li>
It allows nuance to be factored into multiple layers.
Each layer, or 'transformation',
includes an operation type (e.g. tempo control),
a PFT, and a note selector.
A transformation, when applied to a score,
modifies parameters of some or all of the selected notes.
</ul>
<p>
GNM has two broad areas of use.
In the first, a human musician develops a nuance description
for a given score,
using an editing system of some sort.
We call this 'nuance specification'.
This could be used, for example, to create a 'virtual performance' of a work.
We discuss this and other applications in Section X.
<p>
The second area, 'nuance inference',
involves taking a score for a work
and a set of human performances of the work,
and finding, algorithmically and/or manually,
nuance descriptions that closely approximate the human performances.
This is discussed in Section X.
<p>
The remainder of the paper explores the above topics.
Section X discusses related work,
and Section X discusses future work and conclusions.

<h2>2. The GNM model</h2>
<p>
GNM is based on an abstract model with two classes:
$score, which represents the basic parts of a musical work,
and $nuance_desc, which represents a nuance description.
GNM defines the structure of these classes,
and the semantics of applying a nuance description to a score.
GNM doesn't specify how the classes are implemented.
<p>
We developed a \"reference implementation\" of GNM in Python
(Section X).
For this reason, we describe GNM in terms of Python data structures
and functions.
However, GNM could be implemented using other languages
or data representations.
These implementations could then be integrated into 
score editors, music programming languages,
or other computer music systems.

<h3>2.1 Time</h3>
<p>
GNM uses two notions of time:
<p>
<i>Score time</i>: time as notated in a score,
represented as floating-point numbers.
The scale is arbitrary;
our convention is that the unit is a 4-beat measure.
Thus, the duration of a quarter note is 1/4, or 0.25.
<p>
 <i>Adjusted time</i>: a transformed version of score time.
In the final result of applying an GNM description to a score,
adjusted time is real time, measured in seconds.

<p>

<h3>2.2 $score</h3>
<p>
The class $score represents the basic parts of a musical work:
note pitches and notated timings, and measure boundaries if present.
A $score could correspond to a MusicXML file,
a Music21 object hierarchy, or a MIDI file.
These embodiments may contain additional information &mdash;
slurs, accent marks, dynamic markings, note stem directions, etc. &mdash;
that are not considered part of the $score.

<p>
The class $note represents a note.
The attributes of a $note N include:
<ul>
<li> Its start time and duration in score time
(`N.time` and `N.dur`)
and its adjusted start time and duration
(`N.adj_time` and `N.adj_dur`).

<li> Its pitch `N.pitch` (represented, for example, as a MIDI pitch number).
<li> `N.tags`: A set of textual 'tags'.
Tags are assigned both implicitly or explicitly (see section X).
</ul>
<p>
A $note N has attributes that are implicit,
based on its context in the score:
<p>
<ul>
<li> Tags `top` or `bottom` are added if N
has the highest or lowest pitch of notes starting at the same time.
<li> `N.nchord` is the number of notes
with the same start time as N, and `N.nchord_pos` is N's pitch order in this set
(0 = lowest, 1 = 2nd lowest, etc.).
</ul>

<p>
The class $measure represents a measure.
Each measure is described by its start time and duration,
in units of score time.
Measures must be non-overlapping.
Like Notes, Measures can have textual tags.
By convention, these tags
include a string representing the measure's
duration and metric structure.
For example, '2+2+3/8' might represent a 7/8 measure
grouped as 2, 2, and 3 eighths.
<p>
If a note N lies within a measure M, N has two additional attributes:
<ul>
<li>
`N.measure_offset`: the note's time offset from the start of M.
<li>
`N.measure`: a reference to M.
</ul>
<p>
If a note lies on the boundary between two measures,
it's considered to be in the second one.

<h3>2.3 Tags</h3>
<p>
Note tags provide a way to specify
the set of notes to which a nuance gesture is to be applied.
<p>
Implicit tags (like N.top) are conceptually part of the score.
A nuance description can also add 'explicit tags',
which are conceptually part of the nuance description.
For example, `rh` and `lh` could be used to tag
notes in the right and left hand parts.
In a fugue, tags could indicate that a note is part of the fugue theme,
or a particular instance of the theme.
Tags could indicate the harmonic function of notes;
e.g. that a note part of a dominant chord in a cadence,
or that it is the 7th in a major seventh chord.
Tags could used to identify hierarchical
(large, medium, and small-scale) structural components of a work.
Tags could indicate the presence
of accent and articulation marks (dots, wedges).
<p>
GNM does not specify or restrict how tags are assigned.
It could be done manually by a human nuance creator,
or automatically by the software system in which GNM is embedded.

<h3>2.4 Note selectors</h3>
<p>
A <i>note selector</i> is a Boolean-valued function of a note.
A note selector F identifies a set of notes within a $score,
namely the notes N for which F(N) is True.
<p>
We use Python syntax for these expressions.
For example, the function
<p>
<pre>
    lambda n: 'rh' in n.tags and n.dur == 1/2
</pre>
<p>
selects all half notes in the right hand, and
<pre>
    lambda n: '3/4' in n.measure.tags and n.measure_offset == 2/4
</pre>
selects notes on the 3rd beat of 3/4 measures.
One could select notes in a range of score time,
a range of pitches, and so on.
<p>
In Python, the type of note selectors is
<pre>
    type Selector = Callable[[$note], bool] | None
</pre>
<p>

<h3>2.5 Piecewise functions of time</h3>
<p>
Nuance gestures typically involve values
(such as tempo and volume) that change over time.
In GNM, these are described as functions of score time,
and are specified as a sequence of 'primitives',
each of which describes a function defined
either on a time interval or at a point.
A function defined in this way is called a 'piecewise function of time' (PFT).
<p>
In the reference implementation of GNM,
PFT primitives have types derived from a base class $pft_primitive.
There are two kinds of PFT primitives:
<p>
<i>Interval primitives</i>
describe a function over a time interval $ [0, dt] $ where $ dt ≥ 0 $.
Examples:
<pre>
    class Linear(PFTPrimitive)          # a segment of a linear function
    class ExpCurve(PFTPrimitive)        # a segment of an exponential function
</pre>
Primitives could be defined for other types of functions
(polynomial, trigonometric, spline, etc.).

<p>
<i>Momentary primitives</i> represent a value at a single moment.
Examples:
<pre>
    class Accent(PFTPrimitive)          # an accent (volume)
    class Pause(PFTPrimitive)           # a pause (timing)
    class Shift(PFTPrimitive)           # a time shift (timing)
</pre>
<p>
PFTs are represented by lists of PFT primitives:
<pre>
    type PFT = list[PFTPrimitive]
</pre>

<p>
<p>
GNM uses PFTs for several purposes: tempo, volume,
time shifts, and pedaling.
When a PFT is used to describe tempo (see Section X),
the definite integral of the function or its reciprocal is used.
Thus, primitives used in tempo PFTs must provide member functions
<pre>
    integral(t: float): float           # the integral of F from 0 to t
    integral_reciprocal(t: float): float    # the integral of 1/F from 0 to t
</pre>
<p>
When a PFT is used for other purposes
(volume, time shift, fractional pedaling, and so on)
the function value is used.
If there is a discontinuity in the PFT
(i.e. the ending value of a primitive differs
from the starting value of the next primitive)
one must specify which value is used.
Primitives used for these purposes must provide member functions
<p>
<pre>
    value(t: float): float              # value at time t
    closed_start(): bool                # closure at time 0
    closed_end(): bool                  # closure at time dt
</pre>
<p>
For example, a volume function might be defined by the PFT:
<pre>
   [
       Linear(25, 15, 2/1, closed_start = True),
       Linear(15, 20, 1/1, closed_end = True),
       Linear(10, 15, 2/1, closed_start = False)
   ]
</pre>
<p>
defines a function that varies linearly
from 25 to 15 over two 4-beat measures,
from 15 to 20 over one measure,
then from 10 to 15 over two measures.
Its value at the start of the 4th measure is 20
because of the closure arguments.
<p>
<center>
<img src=pft.png width=500>
<br>
Figure 2: A piecewise function of time is a concatenation of primitives.
</center>

<h3>2.5.1 Linear PFT primitive</h3>
<p>
The constructor for `Linear` is:
<p>
<pre>
   Linear(
       y0: float,
       y1: float,
       dt: float,
       closed_start: bool = True,
       closed_end: bool = False
   )
</pre>
<p>
This represents a linear function $\F$ with
$\F(0)=y_0$
and
$\F(dt)=y_1$ .
Its definite integral is
<p>
$$ ∫_0^x F(t)dt = {ax^2}/2 + xy_0 $$

<p>
where $ a $ is the slope

$$ a = {y_1 - y_0}/t $$

<p>
and the definite integral of its reciprocal is
<p>
$$ ∫_0^x 1/{F(t)}dt = {\log(ax + y_0)-\log(y_0)}/a $$

<p>
<h3>2.5.2 Exponential PFT primitive</h3>
<p>
`ExpCurve` is a PFT primitive representing a family of exponential functions
$ F(t) $ that vary from $ y_0 $ to $ y_1 $ over $ [0, Δt] $.
<p>
$$ F(t) = y_0 + {(y_1-y_0)(1-e^{{Ct}/{Δt}})}/{1-e^C} $$
<p>
<p>
C is a curvature parameter.
If C is positive, F is concave up,
and the change is concentrated in the later part of the interval.
If C is negative, F is concave down,
and the change is concentrated in the earlier part of the interval.
If C is zero, F is linear.
Examples are shown in Figure 3.
<p>
<center>
<table>
<tr>
<td><img src=exp2.png width=300></td>
<td><img src=exp5.png width=300></td>
</tr><tr>
<td><img src=exp-2.png width=300></td>
<td><img src=exp-5.png width=300></td>
</tr>
</table>
<p>
Figure 3: Exponential primitives with different curvatures.
</center>

`ExpCurve` is quite versatile.
In developing nuance descriptions for
a range of piano pieces,
we found that ExpCurve (and Linear, a special case) were sufficient
for expressing continuous variations in tempo and volume.
<p>
The definite integral of $ F $ from 0 to $ x $ is
<p>
$$ ∫_0^x F(t)dt = t(y_0 + {Δy(t_{norm} C - e^{(Ct_{norm})} + 1)}/{C(1-e^C)}) $$

where

$$
t_{norm} = x/{Δt}
$$
<p>
$$
Δy = y_1 - y_0
$$
<p>
The indefinite integral of $ 1/F $ is
<p>
$$
G(x) = ∫_0^x 1/{F(t)}dt = {(e^C - 1)(tC - log(|\; y_0(e^C-1) + Δy(e^{Ct} - 1)|))} / {Cy_0(e^C-1) - Δy} $$
<p>
and hence the definite integral of $ 1/F $ from 0 to $ x $ is
<p>
$$ ∫_0^x 1/{F(t)}dt = G(t) - G(0) $$



<h3>2.5.3 Momentary PFT primitives</h3>
<p>
GNM defines several momentary primitives, used for different purposes.
<pre>
   Accent(value: float)
</pre>

Represents a volume adjustment for notes starting
at a particular time (see Section X).
The surrounding interval segments must be open at their respective ends.

<pre>
   Pause(value: float, after: bool)
</pre>
This is used in tempo PFTs
to represent a pause of the given duration,
in units of adjusted time.
If `after` is True, the pause occurs
after the events at the current time;
otherwise it occurs before them.
There can be pauses of both types (before and after)
at a particular time.
Pauses shift the start times of all subsequent events;
see Section 3.1.

<pre>
   Shift(value: float)
</pre>
This represents a shift in the adjusted times of events at the current time.
This can be used for 'agogic accents',
in which melody notes are brought out by
shifting them slightly after accompaniment notes;
see Section 3.2.
Unlike `Pause`, subsequent events are not affected.

<h3>2.6  Transformations</h3>
<p>
A GNM specification consists of a sequence of <i>transformations</i>.
A transformation acts on a $score, modifying it in some way.
Each transformation includes an \"operator\"
indicating the type of the transformation.
The set of operators is listed in the following sections.
Transformations are notated as member functions of the $score class;
each function is an operator.
<p>
These functions have additional parameters.
Most have an optional note selector, indicating what notes are affected.
Many operators include a PFT
and a time offset $ t_0 $ indicating the score time
at which the transformation starts.
<p>
Other tranformations including a function argument
mapping a $note to number
(indicating, for a example, a volume adjustment).
These arguments have the type
<pre>
    type NoteToFloat = Callable[[Note], float]
</pre>

<h2>3. Timing</h2>
<p>
GNM supports three kinds of timing adjustment.
<p>
<b>Tempo control</b>: The adjusted times of
events (note and pedal starts and ends)
are changed according to a 'tempo function',
which can include pauses before and/or after particular score times.
Tempo functions are represented as PFTs.
<p>
<b>Time shifting</b>.
Note starts can be shifted earlier or later in adjusted time.
Other notes are not changed
(unlike pauses, which postpone all subsequent notes).
GNM defines several time-shift transformations:
for example,
'rolling' a chord with specified shifts for each chord note,
or using a PFT to specify 'agogic accents'
in which melody notes are played slightly before or after accompaniment notes.
<p>
<b>Articulation control:</b> Note durations
(in either score time or adjusted time)
can be scaled or set to particular values,
to express legato, portamento, and staccato.
This can be done in various ways,
including continuous variation of articulation using a PFT.

<p>
These adjustments can be layered.
For example, one could specify several layers of tempo adjustment,
followed by time shifting.

<h3>3.1 Tempo control</h3>
<p>
Tempo variation is described with a PFT.
GNM provides three options for the meaning of this PFT:
<ul>
<li> Slowness (or inverse tempo):
The PFT value is the rate of change of adjusted time
with respect to score time.
If $ t_0 $ and $ t_1 $ are score times,
the PFT scales the interval from $ t_0 $ to $ t_1 $
by the average value of the PFT between those times.
Thus, larger means slower.
<li> Tempo:
the PFT value is the rate of change of score time
with respect to adjusted time.
Score time is scaled
based on the integral of the reciprocal of the PFT.
Larger means faster.
<li> Pseudo-tempo:
an approximation to tempo for PFT primitive types for which
it's infeasible to compute the integral of the reciprocal.
Instead, the tempo parameters of the PFT primitives are inverted,
and use the result is used as a slowness function.
</ul>
<p>
In all cases, the PFT can include momentary `Pause` primitives.
These represent pauses; their value is in units of adjusted time.
They are like Dirac delta functions.
<p>
<pre>
    # modes
    TIME_TEMPO = 0
    TIME_PSEUDO_TEMPO = 1
    TIME_SLOWNESS = 2

    Score.tempo_adjust_pft(
        pft: PFT,
        t0: float,
        selector: Selector,
        normalize: bool,
        mode: int
    )
</pre>
<p>
This modifies the adjusted time of the selected notes,
starting at score time $ t_0 $,
according to the timing function specified by the PFT.

<p>
If `normalize` is set, the tempo adjustment is scaled
so that its average value is one;
in other words, the adjusted times of the start and end points remain fixed,
but events between them can move.
This can be used, for example, to apply rubato to
a particular voice over a given period,
and have the voice synch up with other voices at the end of that period.
<p>
<img src=chopin.png>
<p>
The implementation of `tempo_adjust_pft()`, somewhat simplified,
is as follows (see Figure X):
<p>
<ul>
<li> Make a list of all 'events' (starts and ends of selected notes
pedal applications)
ordered by score time.
Each event has a score time and an adjusted time.
<li> Scan this list, processing events 
that lie within the domain of the PFT.
<li> For each pair of consecutive events E1 and E2,
compute the average A of the PFT between the score times of E1 and E2
(i.e. the integral of the PFT over this interval divided by the interval size).
<li>
Let dt be the difference in original adjusted time between E1 and E2.
Change the adjusted time of E2 to be
the (updated) adjusted time of E1 plus A*dt.
<li>
What about pauses?
<p>
before: Earlier notes that end at or after t are elongated.
<p>
after: Notes that start at t are elongated.
</ul>
<center>
<img src=timing.png width=600>
<br>
<b>Figure 1: The semantics of tempo adjustment.</b>
</center>
<p>

<h3>3.2 Time shifts</h3>
<p>
These transformations modify the adjusted start times of notes.
The adjusted durations are modified to preserve the end times.
<p>

<pre>
    Score.time_shift_pft(
        pft: PFT,
        t0: float = 0,
        selector: Selector
    )
</pre>
For notes N that satisfy the selector and lie in the domain of the PFT,
add `pft.value(N.time - t0)` to `N.adj_time`.
This can be used to give agogic accents to notes at particular times,
or to shift notes by continuously-varying amounts.
<pre>
    Score.roll(
        t: float,
        offsets: list[float],
        is_up: bool = True,
        selector: Selector
    )
</pre>
<p>
\"Roll\" the chord at the given time.
`offsets` is a list of time offsets.
These offsets are added to the adjusted start times of notes
that start at score time `t`.
If `is_up` is true, the offsets are applied from the bottom pitch upwards;
otherwise from top pitch downward.
<p>
<pre>
    Score.t_adjust_list(
        offsets: list[float],
        selector: Selector
    )
</pre>
<p>
`offsets` is a list of adjusted-time offsets.
They are added to the start times of notes satisfying the selector,
in time order.
<p>
<pre>
    Score.t_adjust_notes(
        offset: float,
        selector: Selector
    )
</pre>
<p>
The given adjusted-time offset is added to the start times of
all notes satisfying the selector.
<p>
<pre>
    Score.t_adjust_func(
        f: NotetoFloat,
        selector: Selector
    )
</pre>
<p>
For each note N satisfying the selector,
add `f(N)` to `N.adj_time`.
For example, the following adds Gaussian jitter to note start times:
<pre>
    s.t_adjust_func(lambda n: .005*numpy.random.normal(), None)
</pre>
<p>

<h3>3.3. Articulation</h3>
<p>
These transformations modify the adjusted-time duration of notes.
<pre>
    Score.perf_dur_rel(
        factor: float,
        selector: Selector
    )
</pre>
<p>
Multiplies the duration of the selected notes by the given factor.
<p>
<pre>
    Score.perf_dur_abs(
        dur: float,
        selector: Selector
    )
</pre>
<p>
Sets the duration of the selected notes to the given value.
<p>
<pre>
    Score.perf_dur_func(
        f: NotetoFloat,
        selector: Selector
    )
</pre>
<p>
Set the duration of selected notes `N` to `f(N)`.

<h3>3.4 Layering timing transformations</h3>
<p>
PFT-based timing transformations without pauses commute,
so the order in which they're applied doesn't matter.
Other transformations generally don't commute.
A typical order of transformations (see Section X):
<ul>
<li> Non-pause tempo transformations
<li> Pause transformations
<li> Shift transformations
<li> Articulation transformations
</ul>

<h2>4. Pedal control</h2>
<h3>4.1 Standard pedals</h3>
<p>
Grand pianos typically have three pedals:
<ul>
<li> <b>Sustain pedal</b>: when fully depressed, the dampers are lifted so that
a) notes continue to sound after their key is released, and
b) all strings vibrate sympathetically.
If the pedal is gradually raised, the dampers are gradually lowered;
this 'half-pedaling' (or more generally, fractional pedaling)
can be used to create various effects.
<li> <b>Sostenuto pedal</b>: like the sustain pedal,
but when it is depressed only the dampers
of currently depressed keys remain lifted.
Half-pedaling works similarly to the sustain pedal.
<li> <b>Soft pedal</b>: the hammers are shifted so that they
hit only 2 of the 3 strings of treble notes.
This reduces loudness and typically softens the timbre.
Fractional pedaling can also be used; its effects vary between pianos.
</ul>
<p>
Some MIDI synthesizers, including PianoTeq, implement all three pedal types;
some also implement fractional pedaling.
<p>
Pedaling, including fractional pedaling,
is critical to the sound of a performance,
but few scores notate it at all,
much less completely and precisely.
Notation of fractional pedal is very rare.
<p>
GNM provides a mechanism for specifying pedal use.
The level of a particular pedal can be specified as a PFT
consisting of `Linear` primitives with values in $ [0,1] $,
where 1 means the pedal is fully depressed
and 0 means it's lifted.
<p>
When a pedal change is simultaneous with note starts,
we need to be able to specify
whether the change occurs before or after the notes are played.
For sustain and sostenuto pedals,
we also need to be able to specify momentary lifting of the pedal.
We handle these requirements using the closure attributes
of PFT primitives.
Suppose that P0 and P1 are consecutive primitives;
P0 ends and P1 begins at time t,
and one or more notes start at t.
The semantics of the PFT depend on the closure of P0 and P1 as follows
(`P1.y0` is the initial value of P1):
<p>
<pre>
end of P0   start of P1     Semantics
-------------------------------------
open        open            lift pedal, play notes, pedal=P1.y0
open        closed          lift pedal, pedal=P1.y0, play notes
closed      open            play notes
closed      closed          play notes, pedal=P1.y0
</pre>

<p>
The `Linear` primitive allows expression of
continuously-changing fractional pedal.
For example,
<pre>
    Linear(4/4, 1.0, .5)
</pre>
produces a pedal change from fully depressed to half depressed
over 4 beats.
If GNM is generating MIDI output this produces
a sequence of continuous-controller commands
with values ranging from 127 to 64.

<p>
To apply a pedal PFT to a $score starting at score time $ t_0 $:
<pre>
    Score.pedal_pft(
        pft: PFT,
        type: PedalType,
        t0: float
    )
</pre>

<h3>4.2 Virtual sustain pedals</h3>
<p>
Sometimes it's useful to sustain only certain keys (pitches).
The sustain pedal can't do this: it affects all keys.
The sostenuto pedal affects a subset of keys,
but its semantics limit its use.
GNM has a mechanism called <i>virtual sustain pedal</i>
that is like a sustain pedal that affects only a specific subset of keys.

<p>
The use of a virtual sustain pedal
is specified by the same type of PFT as for standard pedals;
the only allowed values are 0 (pedal off) or 1 (pedal on).
Such a PFT is applied to a score with
<pre>
    Score.vsustain_pft(
        pft: PFT,
        t0: float,
        selector: Selector
    )
</pre>
If a note N is selected,
and the pedal is on at its start time,
`N.dur` is adjusted so that N is sustained at least until the
pedal is released.
<p>
Virtual sustain pedals can be used,
for example, to sustain an accompaniment figure
without affecting the melody.
<p>
Example?
<p>
Compared to standard sustain pedals,
virtual sustain pedals are more flexible
in terms of what keys are sustained.
They lack two features of standard pedals: there is no fractional pedal,
and, acoustically, there is no sympathetic resonance of open strings.

<h3>4.3 Implementation and layering</h3>
<p>
Pedal specifications must precede timing adjustments.
Timing adjustments (including time shifts)
must affect pedal usage as well as notes.
For virtual pedals this happens automatically.
For standard pedals, if a note at time T is shifted backward,
pedals active at T are shifted backward by the same amount.

<p>
Uses of the standard pedals can't be layered;
that is, two PFTs controlling a particular pedal can't overlap in time.
However, virtual sustain PFTs can overlap standard pedal PFTs.

<h2>5. Dynamics</h2>
<p>
In GNM, the volume of a note is represented by floating point number
in [0..1] (soft to loud).
This may be mapped to a MIDI velocity (0..127),
in which case the actual loudness depends on the synthesis engine.
Notes initially have volume 0.5.
<p>
GNM provides three modes of volume adjustment.
In each case there is an adjustment factor X,
which may vary over time.

<ul>
<li> VOL_MULT: the note volume is multiplied by X,
which typically is in [0..2].
This maps the default volume 0.5 to the full range [0..1].
These adjustments are commutative.

<li> VOL_ADD: X is added to the note volume.
X is typically around .1 or .2.
This is useful for bringing out melody notes when the overall volume is low.

<li> VOL_SET: the note volume is set to X/2. X is in [0, 2].
(The division by 2 means that the scale is the same as as for VOL_MULT).

</ul>

Multiple adjustments can result in levels > 1;
if this happens, a warning is generated and the volume is set to 1.

<p>
The transformation
<p>
<pre>
    Score.vol_adjust_pft(
        mode: int,
        pft: PFT,
        t0: float,
        selector: Selector
    )
</pre>
<p>
adjusts the volume of a set of notes according to a function of time.
`pft` is a PFT,
and `selector` is a note selector
The volume of a selected note N
in the domain of the PFT is adjusted by the factor pft(t),
where t is N.time - t0.
<p>
This can be used to set the overall volume of the piece.
It can be used to shape the dynamics of an inner voice by selecting
the tag used for that voice.
<p>
Other transformations adjust volume explicitly
(not necessarily as a function of time).
<pre>
    Score.vol_adjust(
        mode: int,
        factor: float,
        selector: Selector
    )
    Score.vol_adjust_func(
        mode: int,
        func: NoteToFloat,
        selector: Selector
    )
</pre>
These adjust the volumes of the selected notes.
For `vol_adjust_func()`, the 2nd argument is a function,
its argument is a $note and it returns an adjustment factor.
For example,
<p>
<pre>
    score.vol_adjust(lambda n: random.normal()*.01)
</pre>
<p>
makes a small normally-distributed adjustment to the volume of all notes.
<p>
<pre>
    score.vol_adjust(ns, .9, lambda n: n.measure_offset == 2)
    score.vol_adjust(ns, .8, lambda n: n.measure_offset in [1,3])
    score.vol_adjust(ns, .7, lambda n: n.measure_offset not in [0,1,2,3])
</pre>
<p>
emphasizes the strong beats of 4/4 measures.

<h3>5.1 Layering volume transformations</h3>
<p>
Volume transformations are typically layered (see Section X).
Multiplicative transformations commute,
so the order in which they're applied doesn't matter.
Other transformations generally do not commute.
Typically the order is:
<ul>
<li> one or more transformations with mode VOL_MULT;
<li> transformations with mode VOL_ADD;
<li> transformations with mode VOL_SET.
</ul>

<h2>6. Specifying nuance</h2>
<p>
In many applications of GNM, a human musician (composer or performer)
manually creates a nuance description for a work.
We call this process 'nuance specification'.
<p>
Developing a nuanced specification of a work is
analogous to practicing on a physical instrument.
You start by developing a mental model of a rendition:
how you want the piece to sound.
Then you create a 'rough draft' of a nuance description:
a set of transformations and their roles.
<p>
This is followed by an iterative process:
<ul>
<li> Listen to part of the rendition;
<li> Identify a deviation from the mental model;
<li> Identify and change the relevant part of the nuance description.
</ul>
<p>
This cycle may be repeated thousands of times,
so it's important that it be as easy as possible
(in terms of mouse clicks).
We discuss this in Section X below.
<p>
We created nuance specifications
for piano pieces in a variety of styles,
with the goal of creating expressive and human-like virtual performances.
In this section we describe some principles that we found useful.
But specifying nuance - like practicing for a physical performance -
is a personal process.

<h3>6.1 Nuance structure</h3>
<p>
The first step in developing a nuance specification
is to decide on a structure:
a set of layered transformations, each of which has a particular purpose.
The goal is that when one wants to change something,
it's clear which layer is involved.
<p>
We found that, to do this, we ended up using,
for both timing and dynamics:

<li> One or more layers of continous transformation:
typically a layer at the phrase level (1-8 measures or so)
and a layer at shorter time scale (1-4 beats).
<li> A layer of repeating discrete change
(for example, patterns of accents on the beats within a measure,
or pauses within a measure).
<li> A layer of irregular discrete change
(for example, pauses at phrase ends,
or agogic accents on particular melody notes).
</ul>

<p>
Typically one can use separate controls for the different parts:
for example, the left and right hand parts,
and the melodies.
This can be done by tagging these parts and using not selectors.
<p>
Pedal control:
<ul>
<li> A PFT for the standard sustain pedal.
<li> PFTs for virtual sustain pedals affecting only some voices
(e.g. left or right hand).
</ul>

<h3>6.2 Developing nuance specifications</h3>
<p>
As discussed above, the first step in developing a nuance specification
is to develop a mental model of how you want it to sound.
<p>
Next, you tag notes in the score.
If you want the left and right hands to have independent dynamics,
you could tag notes with `lh` or `rh`.
If you want to bring out or shape the dynamics of melodies,
you'd tag their notes.
If you want to use rubato &mdash; tempo fluctuations
in one voice but not others &mdash; you'd take those notes.
<p>
The way in which notes are tagged depends on the way
in which scores are described.
Numula has a flexible scheme for tagging notes; see section X.
<p>
The next step is to define a nuance structure.
<p>
Finally we come to the hard part:
the listen/edit cycle described earlier.
This can be a daunting task.
We found the following useful:
<ul>
<li> Work on a short part of the piece (say, one measure or phrase).
<li> Work on one voice at a time.
It may or may not be useful to hear other voices at the same time.
<li> Work on one nuance layer at a time.
It may or may not be useful to enable other layers at the same time.
</ul>

<p>
When you're done with a section, it may be useful to
make the nuance into a function (see below)
so that you can reuse it for analogous sections later in the piece.
<p>
In the course of doing low-level editing,
you may decide to make high-level changes,
e.g. to add new note tags for change the nuance structure.

<h2>7. Nuance scripting</h2>
<p>
A long and complex piece typically has repetition at multiple levels.
The nuance for such a piece will typically also have structure.
It's convenient to be able to express:

<li> Repetition.
you might want to define a dynamic pattern
and apply it 16 times in a row,
rather than defining it 16 times.

<li> Parameterization:
transformations parameters can be variables,
and changing the value of a variable affects
all the transformations that use it.
E.g., to emphasize the strong beats in each measure,
one can define a pattern of emphases,
and then apply it to multiple measures.

<li> Functions:
logic &mdash; possibly parameterized,
possibly with loops and conditionals &mdash;
that generates PFTs,
or that applies transformations.

<p>
These are features of every programming language,
so we can achieve these capabilities by
wrapping GNM in a programming language:
i.e., providing an API for describing and layering transformations.
We call this <i>nuance scripting</i>.
We have done this in Numula, using Python (see Section X).
Other languages could be used as well.
<p>
Graphical interfaces for editing nuance
could potentially be enhanced to provide scripting-like capabilities.
We believe that these are necessary for non-trivial applications.

<h2>8. Editing interfaces</h2>
<p>
What kind of UI (user interface) would facilitate creating
and editing nuance specifications &mdash;
in particular, for transcribing one's mental model of a performance?
<p>
This generally involves changing every parameter &mdash;
start time, duration, volume &mdash; of every note.
We can imagine a GUI that shows a piano-roll representation
of the score and lets one click on notes to change their parameters.
This low-level approach would be impossibly tedious.
<p>
Desirable properties of a UI for editing nuance:
<ul>
<li> It can describe nuance at a high level:
an accelerando from 80 to 120 from measures 8 to 13
can be expressed directly rather than by adjusting individual notes.

<li>
One can make an adjustment and hear the effect
quickly and with a minumum of keystrokes and mouse clicks.
</ul>
<p>
Some general approaches:
<ul>
<li> Integrate nuance editing with score editing
(e.g. in Musescore).
Nuance layers are displayed as 'tracks' below the score,
with their PFTs shown graphically.
You can use the mouse to drag and drop nuance primitives,
adjust their parameters, and hear the results.

<p>
It would also be possible to use the nuance to modify
the way the score is displayed:
e.g. to use color or note-head size to express dynamics.

<li> Express nuance in a programming language.
This has been done in Numula (section X).
</ul>
<p>
The low-level editing cycle in Numula was cumbersome;
each adjustment required locating and editing a value in the source code,
then re-running the program
and moving the playback pointer to the relevant time.
This took a dozen or so input events (keystrokers and mouse clicks).
<p>
To accelerate low-level editing, Numula provides a feature called
'Interactive Parameter Adjustment' (IPA)
that reduces the cycle to two keystrokes.
In IPA, you can declare variables to be adjustable,
and you specify their role (tempo, volume).
<p>
You then run the program under an 'IPA interpreter'.
The interpreter lets you specify a start and end time for playback.
You can select an adjustable variable,
change its value with up/arrow keys,
and press the space bar to play the specified part of the pieces.
This reduces the editing cycle to two keystrokes.
<p>
The values of adjustable variables are stored in a file,
which is read when the interpreter is started.

<h3> 8.2 Examples</h3>
<p>
We have used Numula to create nuanced performances
of piano pieces in a variety of styles,
ranging from Beethoven to Berio.
Our goal was to create performances that approximated
performances by a skilled human.
<p>
Appassionata

<h2>9. Applications of nuance specification</h2>
<p>
Let's assume that we have a formalism describing nuance,
and that we have software tools
that make it easy to create and edit 'nuance specifications' for pieces.
These capabilities would have several applications:
<p>
<b>Composition</b>:
As a composer writes a piece,
using a score editor such as MuseScore or Sibelius,
they could also develop a nuance specification for the piece.
The audio rendering function of the score editor could
use this to produce nuanced renditions of the piece.
This would facilitate the composition process
and would convey the composer's musical intentions
to prospective performers.

<p>

<b>Virtual performance</b>:
Performers could create nuanced virtual performances of pieces,
in which they render the piece using a computer
rather than a traditional instrument.

<p>
<b>Performance pedagogy</b>:
A piano teacher's instruction to a student could include
a nuance specification that guides the student's practice.
Feedback could be given in various ways.
For example, as a student practices a piece they could see a
'virtual conductor' that shows, on a screen,
a representation of the target nuance.
Or a 'virtual coach' could make suggestions
(musical and/or technical) to the student based on
the differences between their playing and the nuance specification.

<p>
<b>Ensemble rehearsal and practice</b>:
When an ensemble (say, a piano duo) rehearses together,
they could record their interpretive decisions as a nuance specification.
They could then use this to guide their individual practice
(perhaps using a 'virtual conductor' as described above).

<p>
<b>Automated accompaniment</b>:
an automated accompaniment system could better track a human performer
if it had an approximate description of the nuance
the performer is likely to use.

<p>
<b>Sharing and archival of nuance descriptions</b>:
Web sites like IMSLP [ref]
and MuseScore [ref] let people share scores.
Such sites could also host user-supplied nuance descriptions
for these works.
This would provide a framework for sharing and discussing interpretations.
<p>

<h2>10. Numula</h2>
<p>
Numula is a Python library that implements GNM.
It defines the classes listed above:
$score, $note, PFT, etc.
The transformation functions described in Sections x-y.
are members of the $score class.
<p>
Numula can be used in various ways.
It can be used as a stand-alone system for creating nuanced music
completely in Python.
Or it can be integrated with other systems to provide nuance capabilities;
for example, it can import a MIDI file as a $score object,
and then apply a nuance description to it.
<p>
A $score, after a nuance description has been applied,
can be output as a MIDI file.
Alternatively, Numula can play this MIDI output
using a local Pianoteq server controlled by RPCs.

<h3>10.1 Shorthand notations</h3>
<p>
Numula provides a number of textual 'shorthand notations'
for expressing both scores and different types of PFTs
(tempo, volume, pedal).
For example,
<pre>
    sh_vol('pp 2/4 mf 4/4 pp')
</pre>
returns a PFT representing a crescendo
from pp to mf over 2 beats,
then a diminuendo to pp over 4 beats.
`pp` and `mf` are constants representing
.45 and 1.11 respectively.
<pre>
    sh_tempo('60 8/4 80 4/4 60')
</pre>
returns a PFT for a tempo that varies linearly from 60 to 80 BPM 
beats per minute) over 8 beats, then back to 60 over 4 beats. 
<pre>
    sh_pedal('1/4 (1/4 0 1.) (1/4) 4/4')
</pre>
defines a pedal that off for 1 beat,
changes linearly from off to on over 1 beat,
is on for 1 beat, then off for 4 beats.
<pre>
    sh_score('1/4 c5 d e')
</pre>
returns a Score that plays 3 quarter notes starting at middle C.
The shorthand notation scores has lots of features
that enable compact representation of complex scores;
see [ref].
<p>
All of the shorthand notations have a set of core features:
<p>
Nestable looping:
<pre>
</pre>

Parameterization (using the Python f-string feature).
Instead of hard-wiring PFT parameters,
we can make them into variables.
<pre>
med = 60
faster = 80
sh_tempo(f'{med} 8/4 {faster} 4/4 {med}')
</pre>
<p>
The contents of the {} can be any expression,
including a shorthand notation string.
<pre>
    dv1 = .7
    vm_24 = f' *2 0 1/4 {dv1] 1/4 0'
    vmeas = sh_vol(f' \
        {vmeas} 0 1/1 0 {vmeas}
    ')
</pre>

Measure checking:
<pre>
    sh_vol('
        |1
</pre>

<h3>10.2 Interactive parameter adjustment</h3>
<p>

<h2>11. Nuance inference</h2>
<p>
In the above sections, we have discussed the creation of nuance descriptions
which are applied to a score, producing a rendition.
We now consider the inverse problem:
how to 'infer' the nuance from a performance.
More precisely:
given a score and a performance of the score
(as a sound file or MIDI file)
how to find an GNM nuance description
which, when applied to the score,
closely approximates the performance.
<p>
In this section we present some ideas
on how to make this notion rigorous,
and on its possible applications.
We have not implemented any of the ideas.
<p>
First, we need to restrict the allowable nuance descriptions.
There are nuance descriptions
that exactly reproduce the performance
by specifying the time and volume of each note.
For purposes of stylistic comparison (see below)
such descriptions are not useful.
If a performance has a crescendo,
we want to represent it as a single entity.
<p>
So we need to refine the problem,
by introducing the notion of the 'complexity' of a nuance description.
The problem then becomes: for a given tolerance T,
what is the least complex nuance description
that generates the performance within that tolerance?
<p>

<h3>11.1 Terminology</h3>
<p>
Tagged score
<p>
Constrained nuance structure
<p>
Complexity of a spec
<p>
Score rendition, error

<h3>11.1 How to infer nuance</h3>
<p>
Volume:
inuitively, we want to work from long to short:
to identify phrase-level features, then measure-level, then single notes.
So we might start by:
<p>
<li> Identify a segment of the performance where the volume trends up or down.
<li> Find the primitive type (linear, exponential, etc.)
that best fits the volume contour,
and find the best-fit (e.g. least-squares) parameters
<li> Continue, finding more such disjoint segments,
and assembling the resulting primitives into a PFT.
<p>
We can then subtract this volume adjustment from the performance,
leaving a residue.
We then fit shorter (beat- or measure-level) primitives in a similar way.
<p>
From the resulting residue, we fit accents or patterns of accents.
<p>
We can analyze timing in a similar way:
fitting long tempo primitives,
then shorter primitives, then pauses.
<p>
The above processes might be automated, or manual,
or a hybrid of the two.
We might manually find the endpoints of a crescendo,
then let the computer choose the best primitive and parameters.
<p>
We might need to change the nuance structure along the way.

<p>
<h3>11.2 Applications of nuance inference</h3>
<p>
The first step in this process is to extract
nuance from a large set human performances:
<li> Get a corpus of performances as MIDI files,
or audio recordings converted to MIDI by software.
For each performance get a representation of the score,
e.g. as MusicXML or MIDI.
<li> Computationally find the correspondence of notes between
performance and score (there might be mistakes or other noise).

<p>
<h3>11.2.1 Performance style analysis</h3>
<p>
Having a framework for describing nuance would
enable rigorous comparative analysis of performance practice.
<p>
Compare the performance styles of individual performers
playing the same piece.
Infer one to get nuance structure,
then others using same structure.
Find 'nuance signature' of performers.
<p>
We could 
look for stylistic trends from different time periods,
or different countries or conservatories.

<h3>11.2.2 PFT primitive selection</h3>
<p>
The goal in designing the set of primitives
is to find a small 'basis set' of transformations,
each with a small number of parameters,
that can achieve the desired specifications &mdash;
for example, that can closely approximate typical human performances.
<p>
Having the mechanism of nuance inference
also lets us evaluate PFT primitives.
We have discussed linear and (more generally) exponential primitives,
but there are many other possibilities:
polynomial, trigonometric, and logarithmic functions, spline curves, etc.
Nuance inference lets us demonstrate whether each of these
is actually used in human performance.
<p>
It may turn out that the optimal set of primitives depends on
the period of the performance,
the period and style of the composition,
the individual performer, and so on.

<h2>12. Related work</h2>
<h3>12.1 Time</h3>
<h3>12.2 Languages</h3>
<p>
FORMULA
Supercollider: Env
HMSL?

<h3>12.2 Musical nuance</h3>
<p>
There have been considerable research into performance nuance,
some of which bears on GNM.
<p>
Statistical
<p>
physical models of rit
Trailing ritard as physical braking.
Quadratic increase in slowness.
Not clear that this fits data better than e.g. exponential.
<p>
auto generation of nuance based on structure
KTH
Todd
The resulting renditions will be generic and boring.
<p>
Clynes Superconductor
FORMULA

There has been some research in this general area.
Some papers study the statistics of deviation from the score,
but not the actual modeling of it.
<p>
Papers that model nuance.
<p>
Papers that define rules for generating nuance.
Clynes, Todd etc.

<h3>12.3 Cascading Style Sheets</h3>
<p>
Cascading Style Sheets (CSS)
is a system for specifying the appearance of web pages [ref].
CSS is analogous to GNM in several ways:
<ul>
<li>
A CSS specification is typically separate from the web page,
which is usually described using HTML and/or Javascript.
When a CSS specification is applied to a web page,
it can change the attributes of the page's elements
(size, color, etc.) and the layout of the page.

<li> CSS files can be \"layered\".
They are applied in a particular order,
and later files can extend or override the effects of earlier ones.

<li> CSS specifications can refer to subsets of the HTML elements
using 'selectors' involving the element tags, classes, and IDs.

<li> CSS preprocessors like SASS [ref]:
variables, expressions, mixins (modules).
Analogous to nuance scripting.
</ul>

<h2>13. Future work</h2>
<p>
GNM is a work in progress.
Each of the pieces described in Section X required new features of GNM,
and this will no doubt continue.


<h3>13.1 Dynamic scores</h3>
<p>
In particular, we anticipate challenges in handling
trills and other ornaments in which the number and timing of notes
is a function of nuance rather than fixed in the score.
<p>
Consider the situation where a long trill (or octave tremolo)
is combined other musical material,
which is subject to timing nuance such as rubato.
Suppose the rate of the trill is fixed.
Then the number of notes in the trill is not fixed;
it varies with the timing nuance.
We call this a 'dynamic score'.
<p>
The current GNM model does not handle dynamic scores.
Doing so would require
a) evaluating the timing nuance to determine the length of the trill;
b) generating the notes in the trill,
which could itself be subject to tempo adjustment.

<p>


<h3>13.2 PFT primitives</h3>
<p>
<h3>13.3 Non-keyboard instruments</h3>
<p>
GNM could be extended to handle scores with multiple instruments.
Note tags could include the instrument type
(e.g. 'violin') and instance (e.g. 'violin 1').
<p>
GNM could be extended to describe note parameters
other than pitch and initial volume.
These might include
a) attack parameters such as bow weight;
b) variation in pitch, timbre or volume during a note;
these could be modeled as PFTs.
<h3>13.4 Uses of AI</h3>
<p>
In theory one could use AI to produce nuance descriptions:
e.g. 'Play Phillip Glass in the style of Arthur Schnabel'.
That is not the goal of this work:
we want to provide tools to human composers and performers
that let them create music with their own expression.
<p>
However, AI might have useful roles.
For example, it could be used
as part of a nuance inference system.
<p>

<h2>14. Conclusion</h2>
<p>
Computers are increasingly important tools for
composition, pedagogy, and performance.

High-resolution nuance specification makes nuance a first-class citizen,
along with scores and sounds.
This will not replace the human component of nuance,
or the spontaneity of live performance;
rather, it will provide tools that can enhance these processes
<p>
Richard Kraft contributed ...

<h2>References</h2>
<p>
<ol>
<li>Malcolm Bilson.
Video: 'Knowing the Score: Do We Know How to Read Urtext Editions and How Can This Lead to Expressive and Passionate Performance?'
Cornell University Press, Ithaca, 2005.
<br>
https://www.youtube.com/watch?v=mVGN_YAX03A
<li> D.P. Anderson and R.J. Kuivila, FORMULA: a Programming Language for Expressive Computer Music, IEEE Computer. June 1991.
<li>
Cascading Style Sheets: https://en.wikipedia.org/wiki/CSS
<li>
PianoTeq: https://en.wikipedia.org/wiki/Pianoteq
<li>
Numula: https://github.com/davidpanderson/numula/
<li>
IMSLP: https://imslp.org
<li>
MuseScore: https://musescore.com
<li>
D. Mazinanian and N. Tsantalis, An Empirical Study on the Use of CSS Preprocessors, 2016 IEEE 23rd International Conference on Software Analysis, Evolution, and Reengineering (SANER), Osaka, Japan, 2016, pp. 168-178, doi: 10.1109/SANER.2016.18.
<li>
Lie, Håkon & Bos, Bert. (1997). Cascading style sheets. World Wide Web Journal. 2. 75-123. 

<li> Supercollider: https://supercollider.github.io/

<li> HMSL: https://www.softsynth.com/hmsl/index.php

<li> Abjad: https://abjad.github.io/.
Python; string notation for scores; algorithmic composition and typesetting.
Scripting for score representation.

</ol>
</div>
</body>
</html>
";

// expand backquotes into <code>...</code>
function expand($s) {
    $n = 0;
    $out = '';
    $start = true;
    while (1) {
        $i = strpos($s, '`', $n);
        if ($i === false) break;
        $out .= substr($s, $n, $i-$n);
        if ($start) {
            $out .= '<code>';
            $start = false;
        } else {
            $out .= '</code>';
            $start = true;
        }
        $n = $i+1;
    }
    $out .= substr($s, $n);
    return $out;
}

echo expand($text);
?>
