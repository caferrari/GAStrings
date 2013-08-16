'use strict';

var populationSize;
var maximumMates;
var population;
var progress = [0,0];

String.prototype.replaceAt=function(index, character) {
    return this.substr(0, index) + character + this.substr(index+character.length);
}

var Chromosome = function(population, gene) {

    var F = function() {}
    F.prototype = Object;
    var that = new F();

    that.population = population;
    that.gene = false;
    that.fitness = 99999;

    that.prototype.calculateFitness = function() {
        var length = this.gene.length;
        this.fitness = 0;
        for (var index=0; index<length; index++) {
            this.fitness += Math.abs(this.gene.charCodeAt(index) - self.objective.charCodeAt(index));
        };
    };

    that.prototype.getRandomChar = function() {
        return String.fromCharCode(Math.floor(Math.random()*90) + 32);
    }

    that.prototype.randomGene = function() {

        var length = self.population.objective.length;

        this.gene = '';
        for (var x = 0; x < length; x++) {
            this.gene += this.getRandomChar();
        }
    };

    that.prototype.mutate = function () {
        var position = Math.floor(Math.random() * this.gene.length);
        var gene = this.gene.replaceAt(position, this.getRandomChar());
        return new Chromosome(gene);
    }

    that.prototype.mate = function(mate) {
        var length = this.gene.length;
        var pivot = Math.floor(Math.random() * (length - 4)) + 2;

        var gene1 = this.gene.substr(0, pivot) + mate.gene.substr(pivot, length);
        var gene2 = mate.gene.substr(0, pivot) + this.gene.substr(pivot, length);

        return [new Chromosome(this.population, gene1), new Chromosome(this.population, gene2)];
    }

    that.prototype.serialize = function() {
        return {gene: this.gene, fitness: this.fitness};
    }

    if (typeof gene === "undefined") {
        that.randomGene();
    } else {
        that.gene = gene;
    }

    that.calculateFitness();

    return that;
}

var Population = function(objective, populationSize, evolutionSteps=100, crossoverProbability=0.8, mutationProbability=0.3) {

    var F = function() {}
    F.prototype = Object;
    var that = new F();

    that.tournamentSize = 3;
    that.population = [];
    that.mutationProbability = mutationProbability;
    that.crossoverProbability = crossoverProbability;
    that.objective = objective;
    that.populationSize = populationSize;
    that.evolutionSteps = evolutionSteps;

    that.prototype.createPopulation = function() {
        var length = self.objective.length;
        this.population = [];
        for (var x = 0; x< self.populationSize; x++) {
            this.population.push(new Chromosome(this));
        }
        this.sortPopulation();

    }

    that.prototype.sortPopulation = function() {
        this.population.sort(
            function(a, b) {
                return a.fitness<b.fitness ? -1 : a.fitness>b.fitness ? 1 : 0;
            }
        );
    }

    that.prototype.cropPopulation = function() {
        this.population = this.population.slice(0, this.populationSize);
    }

    that.prototype.getRandomChromossome = function() {
        var index = Math.floor(Math.random() * this.population.length);
        return this.population[index];
    }

    that.prototype.tournamentSelection = function() {
        var best = this.getRandomChromossome();
        for (var x = 0; x < this.tournamentSize; x++) {
            var adversary = this.getRandomChromossome();
            if (adversary.fitness < best.fitness) {
                best = adversary;
            }
        }

        return best;
    }

    that.prototype.selectParents = function ()
    {
        return [this.tournamentSelection(), this.tournamentSelection()];
    }

    that.prototype.should = function(what) {
        return Math.random() < what;
    }

    that.prototype.evolve = function() {
        for (var evolution=0; evolution < this.evolutionSteps; evolution++) {
            if (this.should(this.crossoverProbability)) {
                var parents = this.selectParents();
                var childrens = parents[0].mate(parents[1]);
                var that = this;
                childrens.forEach(function(child) {
                    if (that.should(that.mutationProbability)) {
                        child = child.mutate();
                    }
                    that.population.push(child);
                });
            }
        }
        this.sortPopulation();
        this.cropPopulation();
    }

    that.prototype.getSerializedData = function() {
        var pop = [];
        this.population.forEach(function(cms) {
            pop.push(cms.serialize());
        })
        return pop;
    }

    return that;

}

self.addEventListener('message', function(e) {
  Object.getOwnPropertyNames(e.data).forEach(function(param) {
    switch (param) {
        case 'workPhrase':
            self.objective = e.data.workPhrase;
            self.postMessage('Objective defined to: ' + self.objective);
            self.population = new Population();
            break;
        case 'populationSize':
            self.populationSize = e.data.populationSize;
            self.postMessage('Population size defined to: ' + populationSize);
            break;
        case 'maximumMates':
            self.maximumMates = e.data.maximumMates;
            self.postMessage('Maximum number of mates defined to: ' + maximumMates);
            break;
        case 'createPopulation':
            self.population = new Population(objective, populationSize);
            self.population.createPopulation();
            self.postMessage('Creating population');
            break;
        case 'start':
            self.postMessage('Starting');
            self.progress = [0, self.maximumMates];
            for (var x=0; x<self.maximumMates; x++) {
                self.population.evolve();
                self.progress[0]++;
            }
            self.postMessage({population: self.population.getSerializedData()});
            break;


        default:
            self.postMessage('Command not found: ' + param);
    }
  });


}, false);
