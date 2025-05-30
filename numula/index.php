<center>
<h2>Specifying nuance in notated keyboard music</h2>

<p>
David P. Anderson
<p>
June 1, 2025
</center>


<h3>Abstract</h3>

We present a model for describing 'nuance'
in the rendition of notated keyboard music:
the deviations (in timing, dynamics, and other parameters)
between a score and a rendition.
The model is designed to allow concise expression
of complex and layered nuance,
as is typically present in human performance.
We discuss the applications of nuance specifications
and the possible interfaces for editing them.

<h3>1. Introduction</h3>
<p>

MNS (Musical Nuance Specification): a model

Numula (Nuance Music Language)
could also be expressed in JSON w/ embedded scripting language

<h3>2. Model</h3>
<p>

<h3>2.1 Scores</h3>
<p>
<h3>2.2 Piecewise functions of time</h3>
<p>

Many components of nuance involve quantities
(like tempo and volume) that change over time.
To describe these, MNS 
A PFT is a list of 'primitives'
'Interval primitives' had nonzero time duration dt,
and typically describe a continuous function
over the interval [0, dt].
'Momentary primitives' have zero duration.
<p>
MNS uses two types of PFTs:
<ul>
<li> 'Integral PFTs': these describe tempo and other timing nuance.
The value of the PFT represents the rate of change
of performance time with respect to score time.
The interval primitives
Each must provide a member function integral(t)
that returns the integral of the primitive from 0 to t.
The momentary primitives are like Dirac deltas
in the tempo function; they represent pauses,
or discontinuities in the map for score time to performance time.

<li> 'Value PFTs': these represent time-varying quantities such
as volume (relative or absolute).
The interval primitives must provice a member function value(t)
return the value at time t.
</ul>


<h3>2.3 Timing</h3>
<h3>2.4 Dynamics</h3>

<h3>3. Numula</h3>
<p>
<h3>4. Examples</h3>
<p>
<h3>5. Editing interfaces</h3>
<p>
<h3>6. Applications</h3>
<p>
=============
Related work
=============
Conclusions
